<?php
namespace tiFy\Lib\Notices;

class Notices
{
    /**
     * Liste des notifications de traitement
     * @var array
     */
    private $Notices = [];

    /**
     * Liste des type de notifications permis. error|warning|info|success par défaut.
     * @var array
     */
    public $AllowedTypes = [];

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        $this->initTypes();
    }

    /**
     * Initialisation des types de notification
     *
     * @param array $types Liste des types permis. error|warning|info|success par défaut.
     *
     * @return void
     */
    final public function initTypes($types = ['error', 'warning', 'info', 'success'])
    {
        $this->AllowedTypes = (array)$types;
        foreach ($this->AllowedTypes as $type) :
            $this->Notices[$type] = [];
        endforeach;
    }

    /**
     * Vérification d'existance de notification de traitement
     *
     * @param string $type Type de notication. error|warning|info|success par défaut.
     * @param null|string $code Code d'identification de qualification de la notification.
     *
     * @return bool
     */
    final public function has($type, $code = null)
    {
        if (!in_array($type, $this->AllowedTypes)) :
            return false;
        endif;

        if (!$code) :
            return !empty($this->Notices[$type]);
        else :
            return isset($this->Notices[$type][$code]);
        endif;
    }

    /**
     * Récupération de la liste des notifications de traitement par type
     *
     * @param null|string $type Type de notication. error|warning|info|success par défaut.
     *
     * @return array
     */
    final public function getList($type = null)
    {
        if (!$type) :
            return $this->Notices;
        endif;

        if (!in_array($type, $this->AllowedTypes)) :
            return false;
        endif;

        return $this->Notices[$type];
    }

    /**
     * Récupération d'un message de notification
     *
     * @param null|string $code Code d'identification de qualification de la notification.
     * @param string $type Type de notication. error|warning|info|success par défaut.
     *
     * @return string
     */
    final public function getMessage($code = null, $type)
    {
        if (!in_array($type, $this->AllowedTypes)) :
            return false;
        endif;

        if ($items = $this->getList($type)) :
            if ($code) :
                if(isset($items['code'])) :
                    return $items['code']['message'];
                endif;
            else :
                $item = current($items);
                return $item['message'];
            endif;
        endif;
    }

    /**
     * Ajout d'une notification de traitement
     *
     * @param string $message Message de notification
     * @param null|string $code Code d'identification de qualification de la notification. La définition du code permet de surcharger ou supprimer une notification prédéfinie.
     * @param array $datas Liste des données associées à une notification.
     * @param string $type Type de notication. info par défaut.
     *
     * @return void
     */
    final public function add($message, $code = null, $datas = [], $type = 'info')
    {
        if (!in_array($type, $this->AllowedTypes)) :
            return false;
        endif;

        if ($code) :
            $this->Notices[$type][$code] = compact('message', 'datas');
        else :
            $this->Notices[$type][] = compact('message', 'datas');
        endif;
    }

    /**
     * Vérification d'existance d'erreurs
     *
     * @param null|string $code Code d'identification de qualification de la notification.
     *
     * @return bool
     */
    final public function hasError($code = null)
    {
        return $this->has('error', $code);
    }

    /**
     * Récupération de la liste des erreurs
     *
     * @return array
     */
    final public function getErrorList()
    {
        return $this->getList('error');
    }

    /**
     * Récupération d'un message d'erreur
     *
     * @param null|string $code Code d'identification de qualification de la notification.
     *
     * @return string
     */
    final public function getErrorMessage($code = null)
    {
        return $this->getMessage($code, 'error');
    }

    /**
     * Ajout d'une erreur
     *
     * @param string $message Message de l'erreur
     * @param string $code Code d'identification de qualification de l'erreur. La définition du code permet de surcharger ou supprimer une erreur déjà définie.
     * @param array $datas Liste des données associées à l'erreur.
     *
     * @return void
     */
    final public function addError($message, $code = null, $datas = [])
    {
        return $this->add($message, $code, $datas, 'error');
    }

    /**
     * Vérification d'existance d'avertissement
     *
     * @param null|string $code Code d'identification de qualification de l'avertissement.
     *
     * @return bool
     */
    final public function hasWarning($code = null)
    {
        return $this->has('warning', $code);
    }

    /**
     * Récupération des avertissements
     *
     * @return array
     */
    final public function getWarningList()
    {
        return $this->getList('warning');
    }

    /**
     * Récupération d'un message d'avertissement
     *
     * @param null|string $code Code d'identification de qualification de l'avertissement.
     *
     * @return string
     */
    final public function getWarningMessage($code = null)
    {
        return $this->getMessage($code, 'warning');
    }

    /**
     * Ajout d'un avertissement
     *
     * @param string $message Message de l'avertissement
     * @param string $code Code d'identification de qualification de l'avertissement. La définition du code permet de surcharger ou supprimer un avertissement déjà défini.
     * @param array $datas Liste des données associées à l'avertissement.
     *
     * @return void
     */
    final public function addWarning($message, $code = null, $datas = [])
    {
        return $this->add($message, $code, $datas, 'warning');
    }

    /**
     * Vérification d'existance d'informations
     *
     * @param null|string $code Code d'identification de qualification de l'information.
     *
     * @return bool
     */
    final public function hasInfo($code = null)
    {
        return $this->has('info', $code);
    }

    /**
     * Récupération de la liste des informations
     *
     * @return array
     */
    final public function getInfoList()
    {
        return $this->getList('info');
    }

    /**
     * Récupération d'un message d'information
     *
     * @param null|string $code Code d'identification de qualification de l'information.
     *
     * @return string
     */
    final public function getInfoMessage($code = null)
    {
        return $this->getMessage($code, 'info');
    }

    /**
     * Ajout d'une information
     *
     * @param string $message Message d'information
     * @param string $code Code d'identification de qualification de l'information. La définition du code permet de surcharger ou supprimer une information déjà définie.
     * @param array $datas Liste des données associées à une information.
     *
     * @return void
     */
    final public function addInfo($message, $code = null, $datas = [])
    {
        return $this->add($message, $code, $datas, 'info');
    }

    /**
     * Vérification d'existance d'une validation
     *
     * @param null|string $code Code d'identification de qualification de la validation.
     *
     * @return bool
     */
    final public function hasSuccess($code = null)
    {
        return $this->has('success', $code);
    }

    /**
     * Récupération de la liste des validations
     *
     * @return array
     */
    final public function getSuccessList()
    {
        return $this->getList('success');
    }

    /**
     * Récupération d'un message de validation
     *
     * @param null|string $code Code d'identification de qualification de la validation.
     *
     * @return string
     */
    final public function getSuccessMessage($code = null)
    {
        return $this->getMessage($code, 'success');
    }

    /**
     * Ajout d'une validation
     *
     * @param string $message Message de validation
     * @param string $code Code d'identification de qualification de la validation. La définition du code permet de surcharger ou supprimer une validation déjà définie.
     * @param array $datas Liste des données associées à une validation.
     *
     * @return void
     */
    final public function addSuccess($message, $code = null, $datas = [])
    {
        return $this->add($message, $code, $datas, 'success');
    }
}
