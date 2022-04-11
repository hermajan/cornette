<?php
namespace Cornette\Models;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\ORM\{EntityRepository, NonUniqueResultException, NoResultException};
use Nette\InvalidArgumentException;
use Nette\Utils\Strings;
use Nettrine\ORM\EntityManagerDecorator;

class RouteFacade {
	/** @var EntityManagerDecorator */
	protected EntityManagerDecorator $entityManager;
	
	/** @var EntityRepository */
	protected ObjectRepository|Route|EntityRepository $repository;
	
	/** @var EntityRepository */
	protected ObjectRepository|RouteAddress|EntityRepository $repositoryAddress;
	
	public function __construct(EntityManagerDecorator $entityManager) {
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getRepository(Route::class);
		$this->repositoryAddress = $entityManager->getRepository(RouteAddress::class);
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
		
		unset($parameters["presenter"], $parameters["action"]);
		if(!empty($parameters)) {
			$queryBuilder->andWhere("ra.parameters = cast(:parameters as json)")->setParameter("parameters", array_map("strval", $parameters), Types::JSON);
		}
		
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
		
		$slug = Strings::webalize($slug, "/");
		
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
			
			if(empty($parameters)) {
				$parameters = null;
			}
			
			$routeAddress = new RouteAddress();
			$routeAddress->setRoute($route)
				->setLocale($locale)
				->setSlug($slug)
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
		$queryBuilder = $this->repositoryAddress->createQueryBuilder("ra")
			->where("ra.slug=:slug")->setParameter("slug", $slug);
		
		if(!empty($locale)) {
			$queryBuilder->andWhere("ra.locale=:locale")->setParameter("locale", $locale);
		}
		
		if(!empty($parameters)) {
			$queryBuilder->andWhere("ra.parameters = cast(:parameters as json)")->setParameter("parameters", $parameters, Types::JSON);
		}
		
		try {
			return $queryBuilder->setMaxResults(1)->getQuery()->getSingleResult();
		} catch(NoResultException|NonUniqueResultException $e) {
			return null;
		}
	}
	
	/**
	 * @return Route|object|null
	 */
	public function getRoute(string $presenter, string $action): ?Route {
		return $this->repository->findOneBy(["presenter" => $presenter, "action" => $action]);
	}
}
