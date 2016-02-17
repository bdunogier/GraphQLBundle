<?php

namespace Overblog\GraphQLBundle\Relay\Node;

use GraphQL\Type\Definition\Config;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils;
use Overblog\GraphQLBundle\Definition\FieldInterface;

class GlobalIdField implements FieldInterface
{
    public function toFieldDefinition(array $config)
    {
        Config::validate($config, [
            'name' => Config::STRING | Config::REQUIRED,
            'typeName' => Config::STRING,
            'idFetcher' => Config::CALLBACK
        ]);

        $name = $config['name'];
        $typeName = isset($config['typeName']) ? $config['typeName'] : null;
        $idFetcher = isset($config['idFetcher']) ? $config['idFetcher'] : null;

        return [
            'name' => $name,
            'description' => 'The ID of an object',
            'type' => Type::nonNull(Type::id()),
            'resolve' => function($obj, $args, ResolveInfo $info) use ($idFetcher, $typeName) {
                return GlobalId::toGlobalId(
                    !empty($typeName) ? $typeName : $info->parentType->name,
                    is_callable($idFetcher) ? call_user_func_array($idFetcher, [$obj, $info]) : (is_object($obj) ? $obj->id : $obj['id'])
                );
            }
        ];
    }
}