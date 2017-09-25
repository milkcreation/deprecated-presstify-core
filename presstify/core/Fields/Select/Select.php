<?php
namespace tiFy\Core\Fields\Select;

class Select extends \tiFy\Core\Fields\Factory
{
    /**
     * Instance
     * @var int
     */
    protected static $Instance = 0;

    /**
     * Affichage
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du champ
     *
     *      @var string $id Identifiant de qualification unique du champs
     *      @var string $container_id Identifiant HTML du champ
     *      @var string $container_class Liste des classes HTML
     *      @var array $html_attrs Attribut de la balise HTML
     *      @var string $name Identifiant de traitement de la requête
     *      @var string|array Elements selectionnés
     *      @var array $options {
     *              Liste de selection
     *              modèle #1 : ['Aucun', 'Item 1', 'Item2] La valeur est attribuée automatiquement par incrémentation
     *              modèle #2 : ['none' => 'Aucun', '1' => 'Item1', '2' => 'Item2'] La clé d'index détermine la valeur
     *              modèle #3 @todo : [
     *                  'Item Group 1' => [
     *                      'Item 1.1', 'Item 1.2'
     *                  ],
     *                  'Item Group 2' => [
     *                      'Item 2.1', 'Item 2.2'
     *                  ],
     *              ] La clé d'index de niveau 1 définie l'intitulé du groupe, les valeurs sont définie selon les spécifications du modèle 1 ou du modèle 2
     *      }
     *      @var bool $multiple Activation de la liste de selection multiple
     *
     * }
     *
     * return string
     */
    public static function display($attrs = [])
    {
        ++static::$Instance;

        $defaults = [
            'id'                => 'tiFyCoreFields-Select-' . static::$Instance,
            'container_id'      => 'tiFyCoreFields-Select--' . static::$Instance,
            'container_class'   => '',
            'html_attrs'        => [],
            'name'              => '',
            'selected'          => null,
            'options'           => [],
            'multiple'          => false
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $Field = new static($attrs);

        // Définition de la selection
        $selected = (array)$attrs['selected'];
?>
<select name="<?php echo $Field->getName();?><?php echo $attrs['multiple'] ? '[]' : '' ; ?>" id="<?php echo $Field->getContainerId();?>" class="tiFyCoreFields-Select<?php echo $Field->getContainerClass();?>" <?php echo $Field->getHtmlAttrs();?><?php echo $attrs['multiple'] ? ' multiple':'';?>>
<?php foreach ($attrs['options'] as $value => $label) : ?>
    <option value="<?php echo $value;?>" <?php selected(in_array($value, $selected));?>><?php echo $label;?></option>
<?php endforeach;?>
</select>
<?php
    }
}