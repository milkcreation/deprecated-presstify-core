<?php
namespace tiFy\Core\Fields\Select;

class Select extends \tiFy\Core\Fields\Factory
{
    /**
     * Vérification de selection de la case à cocher
     *
     * @return bool
     */
    public function isSelected()
    {
        $selected = $this->getAttr('selected');
        if (is_string($selected)) :
            $selected = array_map('trim', explode(',', $selected));
        endif;

        return in_array($this->getHtmlAttr('value'), $selected);
    }

    /**
     * Affichage
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $attrs {
     *      Liste des attributs de configuration du champ
     *
     *      @param string $before Contenu placé avant le champ
     *      @param string $after Contenu placé après le champ
     *      @var array $attrs {
     *          Liste des attributs de balise
     *
     *      }
     *      @var string|array $selected Valeur des éléments selectionnés
     *      @var array $options {
     *              Liste de selection d'éléments
     *
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
     * }
     *
     * return string
     */
    public static function display($id = null, $args = [])
    {
        static::$Instance++;

        $defaults = [
            'before'  => '',
            'after'   => '',
            'attrs'        => [
                'id'    => 'tiFyCoreFields-Select--' . static::$Instance
            ],
            'selected'          => null,
            'options'           => [],
            'multiple'          => false
        ];
        $args = \wp_parse_args($args, $defaults);

        // Instanciation
        $field = new static($id, $args);

?><?php $field->before(); ?><select <?php $field->htmlAttrs(); ?>>
<?php foreach ($field->getAttr('options') as $value => $label) : ?>
    <option value="<?php echo $value;?>"<?php echo $field->isSelected($value) ? "selected=\"selected\"": ""; ?>><?php echo $label;?></option>
<?php endforeach;?>
</select><?php $field->after(); ?><?php
    }
}