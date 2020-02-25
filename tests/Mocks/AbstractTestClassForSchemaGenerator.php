<?php


namespace W2w\Test\Apie\Mocks;

abstract class AbstractTestClassForSchemaGenerator
{
    /**
     * @var string|null
     */
    private $value;

    /**
     * @var string|null
     */
    private $anotherValue;

    protected function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    protected function getAnotherValue(): ?string
    {
        return $this->anotherValue;
    }

    public function setAnotherValue(?string $anotherValue): self
    {
        $this->anotherValue = $anotherValue;
        return $this;
    }
}
