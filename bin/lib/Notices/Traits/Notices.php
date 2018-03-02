<?php
namespace tiFy\Lib\Notices\Traits;

trait Notices
{
    /**
     * Liste des notifications de traitement
     * @var array
     */
    private $Notices = [];

    /**
     * Liste des types de notifications permis. error|warning|info|success par défaut.
     * @var array
     */
    private $NoticeTypes = ['error', 'warning', 'info', 'success'];

    /**
     * CONTROLEURS
     */
    /**
     * Définition des types de notification
     *
     * @param array $types Liste des types de notification permis. error|warning|info|success par défaut.
     *
     * @return void
     */
    final public function setNoticeTypes($types = ['error', 'warning', 'info', 'success'])
    {
        $this->NoticeTypes = (array)$types;
    }

    /**
     * Ajout d'un type de notification permi
     *
     * @param string $type Type de notification permis
     *
     * @return void
     */
    final public function addNoticeType($type)
    {
        if (!$this->isNoticeType($type)) :
            array_push($type, $this->NoticeTypes);
        endif;
    }

    /**
     * Vérification de permission pour un type de notification
     *
     * @param string $type Identifiant de qualification du type à vérifier
     *
     * @return bool
     */
    final public function isNoticeType($type)
    {
        return in_array($type, $this->NoticeTypes);
    }

    /**
     * Vérification d'existance de notification de traitement
     *
     * @param string $type Type de notication. error|warning|info|success par défaut
     * @param string $code Code d'identification de qualification de la notification
     *
     * @return bool
     */
    final public function hasNotice($type, $code = '')
    {
        if (!$this->isNoticeType($type)) :
            return false;
        endif;

        if (!$code) :
            return !empty($this->Notices[$type]);
        else :
            return isset($this->Notices[$type][$code]);
        endif;
    }

    /**
     * Ajout d'une notification de traitement
     *
     * @param string $type Type de notification
     * @param string $code Code d'identification de qualification de la notification
     * @param string $message Message de notification
     * @param array $datas Liste des données associées à une notification
     *
     * @return bool
     */
    final public function addNotice($type, $code = null, $message = '', $datas = [])
    {
        if (!$this->isNoticeType($type)) :
            return false;
        endif;

        if (!isset($this->Notices[$type])) :
            $this->Notices[$type] = [];
        endif;

        if ($code) :
            if (!isset($this->Notices[$type][$code])) :
                $this->Notices[$type][$code] = [];
            endif;

            $this->Notices[$type][$code][] = compact('message', 'datas');
        else :
            $this->Notices[$type][][] = compact('message', 'datas');
        endif;

        return true;
    }

    /**
     * Récupération de la liste des notifications de traitement par type
     *
     * @param string $type Type de notification.
     *
     * @return array
     */
    final public function getNoticeList($type, $code = '')
    {
        if (!$this->hasNotice($type)) :
            return [];
        endif;

        if ($code) :
            if(isset($this->Notices[$type][$code])) :
                return $this->Notices[$type][$code];
            else :
                return [];
            endif;
        else :
            return $this->Notices[$type];
        endif;
    }

    /**
     * Récupération des messages de notification
     *
     * @param string $type Type de notification
     * @param string $code Code d'identification de qualification de la notification
     *
     * @return string
     */
    final public function getNoticeMessages($type, $code = null)
    {
        if (!$this->isNoticeType($type)) :
            return false;
        endif;

        $messages = [];
        if ($notices = $this->getNoticeList($type, $code)) :
            foreach ($notices as $code => $items) :
                foreach ($items as $i => $attrs) :
                    $messages[] = $attrs['message'];
                endforeach;
            endforeach;
        endif;

        return $messages;
    }

    /**
     * Récupération des données associé à une notification
     *
     * @param string $type Type de notification
     * @param string $code Code d'identification de qualification de la notification
     *
     * @return string
     */
    final public function getNoticeDatas($type, $code = null)
    {
        if (!$this->isNoticeType($type)) :
            return false;
        endif;

        $datas = [];
        if ($notices = $this->getNoticeList($type, $code)) :
            foreach ($notices as $code => $items) :
                foreach ($items as $i => $attrs) :
                    $datas[] = $attrs['datas'];
                endforeach;
            endforeach;
        endif;

        return $datas;
    }
}
