<?php
namespace Api\V8\Param\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;

#[\AllowDynamicProperties]
class LangOption extends BaseOption
{
    /**
     * @inheritdoc
     */
    public function add(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('lang')
            ->setAllowedTypes('lang', 'string');
    }
}
