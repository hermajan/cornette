<?php
namespace Cornette\Models;

use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Doctrine\ORM\Mapping as ORM;

/**
 * Route
 *
 * @ORM\Table(name="`routes`", uniqueConstraints={@ORM\UniqueConstraint(name="presenter_action", columns={"presenter", "action"})})
 * @ORM\Entity
 */
class Route {
	/**
	 * @var int
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 * @var string
	 * @ORM\Column(name="presenter", type="string", length=191, nullable=false)
	 */
	private $presenter;
	
	/**
	 * @var string
	 * @ORM\Column(name="action", type="string", length=191, nullable=false)
	 */
	private $action;
	
	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="RouteAddress", mappedBy="route", fetch="EAGER")
	 * */
	protected $addresses;
	
	public function __construct() {
		$this->addresses = new ArrayCollection();
	}
	
	public function getId(): int {
		return $this->id;
	}
	
	public function setId(int $id): Route {
		$this->id = $id;
		return $this;
	}
	
	public function getPresenter(): string {
		return $this->presenter;
	}
	
	public function setPresenter(string $presenter): Route {
		$this->presenter = $presenter;
		return $this;
	}
	
	public function getAction(): string {
		return $this->action;
	}
	
	public function setAction(string $action): Route {
		$this->action = $action;
		return $this;
	}
	
	public function getAddresses(): Collection {
		return $this->addresses;
	}
	
	public function setAddresses(Collection $addresses): Route {
		$this->addresses = $addresses;
		return $this;
	}
}
