<?php

namespace Asset;

use Asset\Factory\Folder;
use Asset\Factory\File;
use Asset\Factory\Context;

/**
 * Gestion des ressources web (CSS, JS)
 *
 * @package Asset
 */
abstract class Asset
{
    /**
     * Déclaration d'un dossier
     *
     * @param string $name - Identifiant unique du dossier
     * @param string|\SplFileInfo $path - Chemin vers le dossier
     * @param bool $recursive - Gestion de la récursivité (déclaration automatique des sous dossiers)
     *
     * @return $this
     */
	abstract public function addFolder($name, $path, $recursive);

    /**
     * Déclaration d'un fichier
     *
     * @param string $name - Identifiant unique du fichier
     * @param string|\SplFileInfo $path - Chemin vers le fichier
     *
     * @return $this
     */
	abstract public function addFile($name, $path);

    /**
     * Déclaration d'un contexte
     *
     * @param string $name - Identifiant unique du contexte
     * @param string|callable $condition - Condition du contexte - (natif : @home | custom : function() { return ...})
     * @param array $attrs {
     *      Attributs du contexte
     *      @var bool $min - Activation/Désactivation de la minification
     *      @var bool $concat - Activation/Désactivation de la concaténation
     *      @var bool $livereload - Activation/Désactivation de l'injection en live dans le navigateur
     * }
     *
     * @return $this
     */
	abstract public function addContext($name, $condition, $attrs);

    /**
     * Mise en queue d'un dossier/fichier déclaré
     *
     * @param string $name - Identifiant de la déclaration du @addFolder|@addFile
     * @param string $context - Identifiant de la condition native ou du contexte précédemment déclaré
     * @param array $attrs {
     *      Attributs de la mise en queue
     *      @var array $dep - Tableau indexé des identifiants des dépendances du dossier|fichier mis en queue
     *      @var string $version - Version du dossier|fichier mis en queue
     *      @var bool $footerjs - Mise en queue de script(s) dans le pied page
     *      @var string $media - Type de média d'affichage du/des style(s) (all|print|screen)
     * }
     * @return $this
     */
	abstract public function push($name, $context, $attrs);

    /**
     * @todo Réfléchir au fonctionnement
     */
	abstract public function pull();
}