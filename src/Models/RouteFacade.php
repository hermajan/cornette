<?php

namespace Cornette\Models;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\{Decorator\EntityManagerDecorator, EntityRepository, NonUniqueResultException, NoResultException};
use Nette\Caching\{Cache, Storage};
use Nette\InvalidArgumentException;
use Nette\Utils\Strings;

class RouteFacade {
	/** @var EntityManagerDecorator */
	protected $entityManager;
	
	/** @var EntityRepository<Route> */
	protected $repository;
	
	/** @var EntityRepository<RouteAddress> */
	protected $repositoryAddress;
	
	/** @var Cache */
	protected $cache;
	
	public function __construct(EntityManagerDecorator $entityManager, Storage $storage) {
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getRepository(Route::class);
		$this->repositoryAddress = $entityManager->getRepository(RouteAddress::class);
		$this->cache = new Cache($storage, "cornette-routeFacade");
	}
	
	public function getParametersKey(): string {
		$parametersKey = ":parameters";
		try {
			if($this->entityManager->getConnection()->getDatabasePlatform()->hasNativeJsonType()) {
				$parametersKey = "cast(:parameters as json)";
			}
		} catch(DBALException $e) {
		}
		return $parametersKey;
	}
	
	public function getByParameters(array $parameters): ?RouteAddress {
		$queryBuilder = $this->repositoryAddress->createQueryBuilder("ra")
			->join("ra.route", "r", "r.id=ra.route")
			->where("r.presenter=:presenter")->setParameter("presenter", $parameters["presenter"])
			->andWhere("r.action=:action")->setParameter("action", $parameters["action"])
			->orderBy("ra.added", "desc");
		
		if(isset($parameters["locale"])) {
			$queryBuilder->andWhere("ra.locale=:locale")->setParameter("locale", $parameters["locale"]);
			unset($parameters["locale"]);
		}
		
		if(isset($parameters["id"])) {
			$queryBuilder->andWhere("ra.item=:item")->setParameter("item", $parameters["id"]);
			unset($parameters["id"]);
		} else {
			$queryBuilder->andWhere("ra.item is null");
		}
		
		unset($parameters["presenter"], $parameters["action"]);
//		if(!empty($parameters)) {
//			$queryBuilder->andWhere("ra.parameters = ".$this->getParametersKey())->setParameter("parameters", array_map("strval", $parameters), Types::JSON);
//		}
		
		try {
			return $queryBuilder->setMaxResults(1)->getQuery()->getSingleResult();
		} catch(NoResultException|NonUniqueResultException $e) {
			return null;
		}
	}
	
	public function insertRoute(?string $presenter, string $action, array $parameters, string $slug, string $locale): ?RouteAddress {
		if(!isset($presenter)) {
			throw new InvalidArgumentException("Presenter must have name!");
		}
		
		$slug = Strings::webalize(str_replace([",", "."], "", $slug), "/_");
		
		if(isset($parameters["locale"])) {
			$locale = $parameters["locale"];
			unset($parameters["locale"]);
		}
		
		unset($parameters["presenter"], $parameters["action"]);
		
		/** @var RouteAddress|null $routeAddress */
		$routeAddress = $this->getBySlug($slug, $locale, $parameters);
		if(!isset($routeAddress)) {
			/** @var Route|null $route */
			$route = $this->getRoute($presenter, $action);
			if(!isset($route)) {
				$route = new Route();
				$route->setPresenter($presenter)->setAction($action);
			}
			
			$item = null;
			if(isset($parameters["id"])) {
				$item = $parameters["id"];
				unset($parameters["id"]);
			}
			
			if(empty($parameters)) {
				$parameters = null;
			}
			
			$routeAddress = new RouteAddress();
			$routeAddress->setRoute($route)
				->setLocale($locale)
				->setSlug($slug)
				->setItem($item)
				->setParameters($parameters)
				->setAdded(new \DateTime());
			
			try {
				$this->entityManager->persist($routeAddress);
				$this->entityManager->flush($routeAddress);
			} catch(UniqueConstraintViolationException $e) {
				return null;
			}
		}
		
		return $routeAddress;
	}
	
	public function getBySlug(string $slug, ?string $locale = null, ?array $parameters = null): ?RouteAddress {
		// caching currently not working because of type hints in RouteAddress entity
//		$key = "slug~".$locale."~".$slug;
//		$data = $this->cache->load($key);
//		if($data === null) {
			$queryBuilder = $this->repositoryAddress->createQueryBuilder("ra")
				->where("ra.slug=:slug")->setParameter("slug", $slug);
			
			if(!empty($locale)) {
				$queryBuilder->andWhere("ra.locale=:locale")->setParameter("locale", $locale);
			}
			
			$item = null;
			if(isset($parameters["id"])) {
				$item = $parameters["id"];
				unset($parameters["id"]);
			}
			if(!empty($item)) {
				$queryBuilder->andWhere("ra.item=:item")->setParameter("item", $item);
			}

//		if(!empty($parameters)) {
//			$queryBuilder->andWhere("ra.parameters = ".$this->getParametersKey())->setParameter("parameters", $parameters, Types::JSON);
//		}
			
			try {
				/** @var RouteAddress $data */
				$data = $queryBuilder->setMaxResults(1)->getQuery()->getSingleResult();
//				$this->cache->save($key, $data, [Cache::TAGS => ["routeAddress/".$data->getId()]]);
			} catch(NoResultException|NonUniqueResultException $e) {
				$data = null;
			}
//		}
		
		return $data;
	}
	
	/**
	 * @return Route|object|null
	 */
	public function getRoute(string $presenter, string $action) {
		return $this->repository->findOneBy(["presenter" => $presenter, "action" => $action]);
	}
}
