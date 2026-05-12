<?php

namespace PHPMaker2026\Project1;

use LightSaml\Model\Metadata\EntityDescriptor as BaseEntityDescriptor;
use LightSaml\Model\Context\DeserializationContext;

/**
 * Entity descriptor for SAML2 provider
 */
class EntityDescriptor extends BaseEntityDescriptor
{
    /**
     * @param string $filename
     *
     * @return EntityDescriptor
     */
    public static function load($filename)
    {
        $options = ["ssl" =>
            [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];
        return static::loadXml(file_get_contents($filename, false, stream_context_create($options)));
    }

    /**
     * @param string $xml
     *
     * @return EntityDescriptor
     */
    public static function loadXml($xml)
    {
        $context = new DeserializationContext();
        $context->getDocument()->loadXML($xml);
        $ed = new static();
        $ed->deserialize($context->getDocument(), $context);
        return $ed;
    }
}
