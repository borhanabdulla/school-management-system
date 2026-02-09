<?php

declare(strict_types=1);

namespace App\Infrastructure\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

/**
 * BaseData - الكلاس الأساسي لجميع الـ DTOs
 * 
 * Data Transfer Objects (DTOs) تُستخدم لنقل البيانات بين الطبقات بشكل:
 * - Type-Safe: كل خاصية لها نوع محدد
 * - Immutable: البيانات لا تتغير بعد الإنشاء
 * - Validated: التحقق من البيانات عند الإنشاء
 * 
 * @example
 * // تعريف DTO
 * class StudentData extends BaseData
 * {
 *     public function __construct(
 *         public readonly string $firstName,
 *         public readonly string $lastName,
 *         public readonly string $nationalId,
 *         public readonly ?string $email = null,
 *     ) {}
 * }
 * 
 * // الاستخدام
 * $data = StudentData::fromArray($request->validated());
 * $data = StudentData::fromRequest($request);
 * $array = $data->toArray();
 * $partial = $data->only(['firstName', 'lastName']);
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
abstract class BaseData implements Arrayable, JsonSerializable
{
    /**
     * ═══════════════════════════════════════════════════════════════
     * Factory Methods - طرق الإنشاء
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * إنشاء من مصفوفة
     * 
     * @param array<string, mixed> $data
     * @return static
     * 
     * @example
     * $studentData = StudentData::fromArray([
     *     'firstName' => 'أحمد',
     *     'lastName' => 'محمد',
     *     'nationalId' => '1234567890',
     * ]);
     */
    public static function fromArray(array $data): static
    {
        $reflection = new ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new static();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();

            // تحويل snake_case إلى camelCase
            $snakeName = self::toSnakeCase($name);

            if (array_key_exists($name, $data)) {
                $args[$name] = self::castValue($data[$name], $param);
            } elseif (array_key_exists($snakeName, $data)) {
                $args[$name] = self::castValue($data[$snakeName], $param);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[$name] = $param->getDefaultValue();
            } elseif ($param->allowsNull()) {
                $args[$name] = null;
            } else {
                throw new \InvalidArgumentException(
                    "Missing required field '{$name}' for " . static::class
                );
            }
        }

        return new static(...$args);
    }

    /**
     * إنشاء من Request
     * 
     * @param Request $request
     * @return static
     * 
     * @example
     * $data = StudentData::fromRequest($request);
     */
    public static function fromRequest(Request $request): static
    {
        return static::fromArray($request->all());
    }

    /**
     * إنشاء من البيانات المُتحقق منها
     * 
     * @param Request $request
     * @return static
     */
    public static function fromValidated(Request $request): static
    {
        return static::fromArray($request->validated());
    }

    /**
     * إنشاء من Model
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return static
     */
    public static function fromModel($model): static
    {
        return static::fromArray($model->toArray());
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * Conversion Methods - طرق التحويل
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * تحويل إلى مصفوفة
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $data = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $property->getValue($this);

            // تحويل الكائنات المتداخلة
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            } elseif ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            } elseif (is_object($value) && enum_exists(get_class($value))) {
                $value = $value->value ?? $value->name;
            }

            $data[$name] = $value;
        }

        return $data;
    }

    /**
     * تحويل إلى مصفوفة مع snake_case keys
     * 
     * @return array<string, mixed>
     */
    public function toSnakeArray(): array
    {
        $data = $this->toArray();
        $result = [];

        foreach ($data as $key => $value) {
            $result[self::toSnakeCase($key)] = $value;
        }

        return $result;
    }

    /**
     * الحصول على حقول محددة فقط
     * 
     * @param array<string> $keys
     * @return array<string, mixed>
     * 
     * @example
     * $partial = $data->only(['firstName', 'lastName']);
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->toArray(), array_flip($keys));
    }

    /**
     * الحصول على جميع الحقول ما عدا المحددة
     * 
     * @param array<string> $keys
     * @return array<string, mixed>
     * 
     * @example
     * $partial = $data->except(['password', 'secret']);
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->toArray(), array_flip($keys));
    }

    /**
     * التحقق من وجود قيمة للحقل
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return property_exists($this, $key) && $this->{$key} !== null;
    }

    /**
     * الحصول على قيمة حقل
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->{$key} ?? $default;
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * JSON Serialization
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * تحويل لـ JSON
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * تحويل لـ JSON string
     * 
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * Helper Methods - أدوات مساعدة
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * إنشاء نسخة جديدة مع تعديل بعض القيم
     * 
     * @param array<string, mixed> $overrides
     * @return static
     * 
     * @example
     * $newData = $data->with(['status' => 'active']);
     */
    public function with(array $overrides): static
    {
        return static::fromArray(array_merge($this->toArray(), $overrides));
    }

    /**
     * تحويل camelCase إلى snake_case
     * 
     * @param string $input
     * @return string
     */
    protected static function toSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * تحويل snake_case إلى camelCase
     * 
     * @param string $input
     * @return string
     */
    protected static function toCamelCase(string $input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    /**
     * تحويل القيمة للنوع المطلوب
     * 
     * @param mixed $value
     * @param \ReflectionParameter $param
     * @return mixed
     */
    protected static function castValue(mixed $value, \ReflectionParameter $param): mixed
    {
        if ($value === null) {
            return null;
        }

        $type = $param->getType();

        if (!$type instanceof \ReflectionNamedType) {
            return $value;
        }

        $typeName = $type->getName();

        // تحويل التواريخ
        if ($typeName === \DateTimeInterface::class || $typeName === \DateTime::class || $typeName === \Carbon\Carbon::class) {
            return \Carbon\Carbon::parse($value);
        }

        // تحويل الـ Enums
        if (enum_exists($typeName)) {
            if (is_string($value) || is_int($value)) {
                return $typeName::tryFrom($value) ?? $value;
            }
        }

        // تحويل الـ Nested DTOs
        if (is_subclass_of($typeName, self::class) && is_array($value)) {
            return $typeName::fromArray($value);
        }

        // التحويلات الأساسية
        return match ($typeName) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => (bool) $value,
            'string' => (string) $value,
            'array' => (array) $value,
            default => $value,
        };
    }

    /**
     * Debug representation
     * 
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }
}
