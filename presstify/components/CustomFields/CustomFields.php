<?php

namespace tiFy\Components\CustomFields;

final class CustomFields extends \tiFy\App\Component
{
    /**
     * Liste des classes de rappel de champ personnalisé
     * @var array
     */
    private static $Factory = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Traitement des attributs de configuration
        foreach (['post_type', 'taxonomy'] as $object_type) :
            if (!$object_names = self::tFyAppConfig($object_type)) :
                continue;
            endif;

            foreach ($object_names as $object_name => $fields) :
                if (empty($fields)) :
                    continue;
                endif;

                foreach ($fields as $key => $value) :
                    if (is_int($key)) :
                        $classname = $value;
                        $attrs = [];
                    else :
                        $classname = $key;
                        $attrs = $value;
                    endif;

                    if (\class_exists($classname)) :
                    else :
                        $ObjectType = $this->appUpperName($object_type, false);
                        $classname = "\\tiFy\\Components\\CustomFields\\{$ObjectType}\\{$classname}\\{$classname}";

                        if (!\class_exists($classname)) :
                            continue;
                        endif;
                    endif;

                    new $classname($object_name, $attrs);
                endforeach;
            endforeach;
        endforeach;
    }
}