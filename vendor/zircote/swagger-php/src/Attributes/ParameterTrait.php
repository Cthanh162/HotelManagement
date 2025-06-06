<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Attributes;

use OpenApi\Generator;

trait ParameterTrait
{
    /**
     * @param 'query'|'header'|'path'|'cookie'|null                   $in
     * @param string|class-string|object|null                         $ref
     * @param array<Examples>                                         $examples
     * @param array<MediaType>|JsonContent|XmlContent|Attachable|null $content
     * @param array<string,mixed>|null                                $x
     * @param Attachable[]|null                                       $attachables
     */
    public function __construct(
        ?string $parameter = null,
        ?string $name = null,
        ?string $description = null,
        ?string $in = null,
        ?bool $required = null,
        ?bool $deprecated = null,
        ?bool $allowEmptyValue = null,
        string|object|null $ref = null,
        ?Schema $schema = null,
        mixed $example = Generator::UNDEFINED,
        ?array $examples = null,
        array|JsonContent|XmlContent|Attachable|null $content = null,
        ?string $style = null,
        ?bool $explode = null,
        ?bool $allowReserved = null,
        ?array $spaceDelimited = null,
        ?array $pipeDelimited = null,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    ) {
        parent::__construct([
                'parameter' => $parameter ?? Generator::UNDEFINED,
                'name' => $name ?? Generator::UNDEFINED,
                'description' => $description ?? Generator::UNDEFINED,
                // next two are special as we override the default value for specific Parameter subclasses
                'in' => $in ?? (Generator::isDefault($this->in) ? Generator::UNDEFINED : $this->in),
                'required' => $required ?? (Generator::isDefault($this->required) ? Generator::UNDEFINED : $this->required),
                'deprecated' => $deprecated ?? Generator::UNDEFINED,
                'allowEmptyValue' => $allowEmptyValue ?? Generator::UNDEFINED,
                'ref' => $ref ?? Generator::UNDEFINED,
                'example' => $example,
                'style' => $style ?? Generator::UNDEFINED,
                'explode' => $explode ?? Generator::UNDEFINED,
                'allowReserved' => $allowReserved ?? Generator::UNDEFINED,
                'spaceDelimited' => $spaceDelimited ?? Generator::UNDEFINED,
                'pipeDelimited' => $pipeDelimited ?? Generator::UNDEFINED,
                'x' => $x ?? Generator::UNDEFINED,
                'attachables' => $attachables ?? Generator::UNDEFINED,
                'value' => $this->combine($schema, $examples, $content),
            ]);
    }
}
