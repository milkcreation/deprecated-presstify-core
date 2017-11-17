<?php
namespace tiFy\Core\Ui\Common\Traits;

trait Notices
{
    /**
     * @todo
     */
    /** == Récupération de la notification courante == **/
    public function current_notice()
    {
        if($this->CurrentNotice)
            return $this->CurrentNotice;

        foreach ((array)$this->Notices as $nid => $nattr) :
            if (! isset($_REQUEST[$nattr['query_arg']]) || ($_REQUEST[$nattr['query_arg']] !== $nid))
                continue;
            return $nid;
        endforeach;

        return false;
    }

    /**
     * @todo
     */
    /**
     * Définition de la notification courante
     *
     * @param string $code
     *
     * @return null|string
     */
    public function set_current_notice($code)
    {
        if (isset($this->Notices[$code]))
            return $this->CurrentNotice = $code;
    }
}