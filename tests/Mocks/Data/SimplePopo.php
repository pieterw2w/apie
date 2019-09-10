<?php
namespace W2w\Test\Apie\Mocks\Data;

use DateTime;

class SimplePopo
{
    private $id;

    private $createdAt;

    public $arbitraryField;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(16));
        $this->createdAt = new DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
