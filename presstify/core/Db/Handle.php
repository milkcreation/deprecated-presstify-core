<?php
namespace tiFy\Core\Db;

class Handle
{
    /**
     * @var Factory
     */
    protected $Db;

    /* = CONSTRUCTEUR = */
    public function __construct(Factory $Db)
    {
        $this->Db = $Db;
    }

    /** == Création d'un élément == **/
    final public function record($data = [])
    {
        $primary_key = $this->Db->Primary;

        if (!empty($data[$primary_key]) && $this->Db->select()->count([$primary_key => $data[$primary_key]])) {
            return $this->update($data[$primary_key], $data);
        } else {
            return $this->create($data);
        }
    }

    /**
     * Création d'un nouvel élément
     */
    final public function create($data = [])
    {
        // Extraction des metadonnées
        if (isset($data['item_meta'])) :
            $metas = $data['item_meta'];
            unset($data['item_meta']);
        else :
            $metas = false;
        endif;

        // Formatage des données
        $data = $this->Db->parse()->validate($data);
        $data = array_map('maybe_serialize', $data);

        // Enregistrement de l'élément en base de données
        $this->Db->sql()->insert($this->Db->Name, $data);
        $id = $this->Db->sql()->insert_id;

        // Enregistrement des metadonnées de l'élément en base
        if (is_array($metas) && $this->Db->hasMeta()) :
            foreach ((array)$metas as $meta_key => $meta_value) :
                $this->Db->meta()->update($id, $meta_key, $meta_value);
            endforeach;
        endif;

        return $id;
    }

    /** == Mise à jour d'un élément == **/
    final public function update($id, $data = [])
    {
        // Extraction des metadonnées
        if (isset($data['item_meta'])) :
            $metas = $data['item_meta'];
            unset($data['item_meta']);
        else :
            $metas = false;
        endif;

        // Formatage des données
        $data = $this->Db->parse()->validate($data);
        $data = array_map('maybe_serialize', $data);

        $this->Db->sql()->update($this->Db->Name, $data, [$this->Db->Primary => $id]);

        // Enregistrement des metadonnées de l'élément en base
        if (is_array($metas) && $this->Db->hasMeta()) {
            foreach ((array)$metas as $meta_key => $meta_value) {
                $this->Db->meta()->update($id, $meta_key, $meta_value);
            }
        }

        return $id;
    }

    /** == Suppression d'un élément son id == **/
    public function delete_by_id($id)
    {
        return $this->Db->sql()->delete($this->Db->Name, [$this->Db->Primary => $id], '%d');
    }

    /**
     *
     */
    public function prepare($query, $args)
    {
        return $this->Db->sql()->prepare($query, $args);
    }

    /**
     *
     */
    public function query($query)
    {
        return $this->Db->sql()->query($query);
    }

    /**
     *
     */
    public function replace($data = [], $format = null)
    {
        return $this->Db->sql()->replace($this->Db->getName(), $data, $format);
    }

    /**
     *
     */
    public function delete($where, $where_format = null)
    {
        return $this->Db->sql()->delete($this->Db->getName(), $where, $where_format);
    }


    /** == Valeur de la prochaine clé primaire == **/
    public function next()
    {
        if ($last_insert_id = $this->Db->sql()->query("SELECT LAST_INSERT_ID() FROM {$this->wpdb_table}")) {
            return ++$last_insert_id;
        }
    }
}