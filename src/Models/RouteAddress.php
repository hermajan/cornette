<?php
namespace Cornette\Models;

use Doctrine\ORM\Mapping as ORM;

/**
 * RouteAddress
 *
 * @ORM\Table(name="`route_addresses`", uniqueConstraints={@ORM\UniqueConstraint(name="locale_slug", columns={"locale", "slug"})}, indexes={@ORM\Index(name="route_id", columns={"route_id"})})
 * @ORM\Entity
 */
class RouteAddress {
	/**
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private int $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Route", inversedBy="addresses", cascade={"persist"}, fetch="EAGER")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="route_id", referencedColumnName="id")
	 * })
	 */
	private Route $route;
	
	/**
	 * @ORM\Column(name="locale", type="string", length=10, nullable=false)
	 */
	private string $locale;
	
	/**
	 * @ORM\Column(name="slug", type="string", length=191, nullable=false)
	 */
	private string $slug;
	
	/**
	 * @ORM\Column(name="parameters", type="json", nullable=true)
	 */
	private ?array $parameters;
	
	/**
	 * @ORM\Column(name="indexed", type="boolean", nullable=false, options={"default"="1"})
	 */
	private bool $indexed = true;
	
	/**
	 * @ORM\Column(name="added", type="datetime", nullable=false)
	 */
	private \DateTime $added;
	
	public function __construct() {
		$this->added = new \DateTime();
	}
	
	public function getId(): int {
		return $this->id;
	}
	
	public function getLocale(): string {
		return $this->locale;
	}
	
	public function setLocale(string $locale): RouteAddress {
		$this->locale = $locale;
		return $this;
	}
	
	public function getSlug(): string {
		return $this->slug;
	}
	
	public function setSlug(string $slug): RouteAddress {
		$this->slug = $slug;
		return $this;
	}
	
	public function getParameters(): ?array {
		return $this->parameters;
	}
	
	public function setParameters(?array $parameters): RouteAddress {
		$this->parameters = $parameters;
		return $this;
	}
	
	public function isIndexed(): bool {
		return $this->indexed;
	}
	
	public function setIndexed(bool $indexed): RouteAddress {
		$this->indexed = $indexed;
		return $this;
	}
	
	public function getAdded(): \DateTime {
		return $this->added;
	}
	
	public function setAdded(\DateTime $added): RouteAddress {
		$this->added = $added;
		return $this;
	}
	
	public function getRoute(): Route {
		return $this->route;
	}
	
	public function setRoute(Route $route): RouteAddress {
		$this->route = $route;
		return $this;
	}
}
