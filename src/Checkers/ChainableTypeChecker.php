<?php
namespace Prezly\PropTypes\Checkers;

use Prezly\PropTypes\Exceptions\PropTypeException;
use \Prezly\PropTypes\Checkers\TypeChecker;

final class ChainableTypeChecker implements TypeChecker
{
    /** @var \Prezly\PropTypes\Checkers\TypeChecker */
    private $checker;

    /** @var bool */
    private $is_required;

    /** @var bool */
    private $is_nullable;

    /** @var bool */
    private $is_model;

    /** @var mixed */
    private $default;

    public function __construct(
        TypeChecker $checker,
        bool $is_required = false,
        bool $is_nullable = false,
        bool $is_model = false
    ) {
        $this->checker = $checker;
        $this->is_required = $is_required;
        $this->is_nullable = $is_nullable;
        $this->is_model = $is_model;
    }

    /**
     * @param array $props
     * @param string $prop_name
     * @param string $prop_full_name
     * @return \Prezly\PropTypes\Exceptions\PropTypeException|null Exception is returned if prop type is invalid
     */
    public function validate(array $props, string $prop_name, string $prop_full_name): ?PropTypeException
    {
        if (! array_key_exists($prop_name, $props)) {
            if ($this->is_required) {
                return new PropTypeException(
                    $prop_name,
                    "The property `{$prop_full_name}` is marked as required, but it's not defined."
                );
            }
            return null;
        }

        if ($props[$prop_name] === null) {
            if (! $this->is_nullable) {
                return new PropTypeException(
                    $prop_name,
                    "The property `{$prop_full_name}` is marked as not-null, but its value is `null`."
                );
            }
            return null;
        }

        return $this->checker->validate($props, $prop_name, $prop_full_name);
    }

    public function isRequired(): self
    {
        return new self($this->checker, true, $this->is_nullable, $this->is_model);
    }

    public function isNullable(): self
    {
        return new self($this->checker, $this->is_required, true, $this->is_model);
    }

    public function isModel(): self
    {
        return new self($this->checker, $this->is_required, $this->is_nullable, true);
    }

    /**
     * @param mixed $value
     * @return ChainableTypeChecker
     */
    public function default($value): ChainableTypeChecker
    {
        $this->default = $value;

        return $this;
    }

    /**
     * @return \Prezly\PropTypes\Checkers\TypeChecker[]|null
     */
    public function getFields(): ?array 
    {
        if ($this->checker instanceof ShapeTypeChecker) {
            return $this->checker->getShapeTypes();
        }
        
        return null;
    }

    /**
     * @return \Prezly\PropTypes\Checkers\TypeChecker
     */
    public function getTypeChecker(): TypeChecker
    {
        return $this->checker;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function getIsNullable(): bool
    {
        return $this->is_nullable;
    }

    /**
     * @return bool
     */
    public function getIsRequired(): bool
    {
        return $this->is_required;
    }

    /**
     * @return bool
     */
    public function getIsModel(): bool
    {
        return $this->is_model;
    }
}
