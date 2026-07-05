<?php

namespace App\OpenApi\Extensions;

use App\Exceptions\BusinessException;
use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types as OpenApiTypes;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;

class BusinessExceptionToResponseExtension extends ExceptionToResponseExtension
{
    public function shouldHandle(Type $type)
    {
        return $type instanceof ObjectType && $type->isInstanceOf(BusinessException::class);
    }

    public function toResponse(Type $type)
    {
        $responseBodyType = (new OpenApiTypes\ObjectType)
            ->addProperty('message', (new OpenApiTypes\StringType)->setDescription('Regra de negócio violada.'))
            ->setRequired(['message']);

        return Response::make(400)
            ->setDescription('Regra de negócio violada')
            ->setContent('application/json', Schema::fromType($responseBodyType));
    }
}
