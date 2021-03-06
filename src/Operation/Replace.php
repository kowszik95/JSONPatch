<?php
declare(strict_types=1);

namespace gamringer\JSONPatch\Operation;

use gamringer\JSONPatch\Operation;
use gamringer\JSONPointer\Pointer;
use gamringer\JSONPointer;

class Replace extends Operation implements Atomic
{
    private $value;
    private $previousValue;

    public function __construct(string $path, $value)
    {
        $this->path = $path;
        $this->value = $value;
    }

    public function apply(Pointer $target)
    {
        try {
            $target->get($this->path);
        } catch (JSONPointer\Exception $e) {
            throw new Exception($e->getMessage(), 0, $e);
        }
        
        $this->previousValue = $target->set($this->path, $this->value);
    }

    public function revert(Pointer $target)
    {
        $target->set($this->path, $this->previousValue);
    }

    public static function fromDecodedJSON($operationContent): self
    {
        self::assertValidOperationContent($operationContent);

        return new self($operationContent->path, $operationContent->value);
    }

    private static function assertValidOperationContent($operationContent)
    {
        if (!property_exists($operationContent, 'path') || !property_exists($operationContent, 'value')) {
            throw new Operation\Exception('"Replace" Operations must contain a "path" and "value" member');
        }
    }

    public function __toString(): string
    {
        return json_encode([
            'op' => Operation::OP_REPLACE,
            'path' => $this->path,
            'value' => $this->value,
        ]);
    }
}
