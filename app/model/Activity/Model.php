<?php
namespace App\Model\Activity;

class Model extends \App\Model\Common\GridTableModel
{

    private $pkGroups = array();
    //tohle je pro reditele
    public function __construct(\DibiConnection $connection)
    {
        //odpoved je ve WorksPresenter - model je treba model->getSomeSelection($id);
        parent::__construct($connection, "act_works");
        $this->getSelection()->removeClause("SELECT");
        $this->getSelection()->select("act_works.*, user.firstname as fn, user.lastname as ln, group.name as pk")
            ->leftJoin("user")->on("act_works.user_id = user.id")
            ->leftJoin("group")->on("act_works.act_notebooks_id = group.id")
            ->where("group.pk = ?", 1)
            ->groupBy("act_works.id");

        $this->setPrimaryKey("act_works.id");
        $this->pkGroups = $this->getWorksPkHead();
    }
    //id předsedy předmětové komise
    private function getWorksPkHead(){
        $result = array();
        $data = $this->query("
            SELECT
                user_id, id
            FROM
                `group`
            WHERE
             `group.pk` = '1'")->fetchAll();
        foreach($data as $item){
            $result[$item->id] = $item->user_id;
        }

        return $result;
    }
    public function isWorksPkHead($id_pk, $userId)
    {

        try {
            if($this->pkGroups[$id_pk] == $userId)
                return true;
            return false;
        }
        catch(\Exception $e)
        {
            return false;
        }

    }
    public function getPkUser($userId){
        $result = array();
        $data = $this->query("
            SELECT
                id, user_id, pk
            FROM
                `group`
            WHERE
              pk = 1
        ")->fetchAll();
        foreach($data as $item)
        {
            $result[$item->id] = $item->user_id;
            if($item->user_id == $userId)
            {
                return array("user_id" => $userId, "pk_id" => $item->id);
            }

        }
        return null;
    }
    //tohle je pro ucitele - seznam všech uživateli přístupných činností
    public function getUsersActivity($userId)
    {
        $result = array();
        $user = $this->getPkUser($userId); //tohle je v usermodelu - asi vratit zpet
        if($user != null)
        {
            $data = $this->query("
            SELECT
                act_works.*,
                user.firstname as fn,
                user.lastname as ln,
                group.name as pk
            FROM
                act_works
                LEFT JOIN user ON act_works.user_id = user.id
                LEFT JOIN `group` ON act_works.act_notebooks_id = `group.id`
            WHERE
                act_works.act_notebooks_id = ".$user['pk_id']."
                OR act_works.user_id = ".$userId."
                AND `group.pk` = '1'
            GROUP BY
                act_works.id
            ")->fetchAll();
            foreach($data as $item)
            {
                if($item["user_id"] != $userId)
                    $item["reward"] = "skrytá"; //předseda pk nevidí odměny jiných lidí
                $result[] = $item;
            }
            return $result;
        }
        else {
            $data = $this->query("
            SELECT
                act_works.*,
                user.firstname as fn,
                user.lastname as ln,
                `group.name` as pk
            FROM
                act_works
                LEFT JOIN user ON act_works.user_id = user.id
                LEFT JOIN `group` ON act_works.act_notebooks_id = `group.id`
            WHERE act_works.user_id = " . $userId . "
                AND `group.pk` = '1'
            GROUP BY
                act_works.id
            ")->fetchAll();

            foreach($data as $item)
            {
                $result[] = $item;
            }
            return $result;
        }

    }
    //seznam předmětových komisí
    public function getPKList()
    {
        $result = array();
        $data = $this->query("
            SELECT
                id, name
            FROM
                `group`
            WHERE `group.pk` = '1'
        ")->fetchAll();
        foreach($data as $item)
        {
            $result[$item->id] = $item->name;
        }
        return $result;
    }

    public function getEditableActivity($id)
    {
        $result = array();
        $data = $this->query("
            SELECT
                *
            FROM
                act_works
            WHERE
                id = ".$id
        )->fetchAll();
        foreach($data as $item)
        {
            $result[] = $item;
        }
        return $result["0"];

    }

    public function getListOfPkIAmIn($userId){
        $result = array();
        $data = $this->query("
            SELECT
                group_id, `group.name`
            FROM
                `membership` LEFT JOIN `group` on group_id = id
            WHERE
            group_id > 4 AND
            membership.`user_id` = ".$userId."
        ")->fetchAll();
        foreach($data as $item)
        {
            $result[$item->group_id] = $item->name;
        }
        return $result;
    }

}