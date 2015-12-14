<?php
/**
 * Created by PhpStorm.
 * User: ondra
 * Date: 11.11.15
 * Time: 15:12
 */
namespace App\Model\Activity;

class Usersmodel extends \App\Model\Common\GridTableModel
{
    //tohle je pro reditele
    public function __construct(\DibiConnection $connection)
    {
        //odpoved je ve WorksPresenter - model je treba model->getSomeSelection($id);
        parent::__construct($connection, "membership");
    }
    //vrati pole s indexem id a hodnotou jmeno a prijmeni
    public function getUsersInPk($pkId)
    {
        $result = array();
        $data = $this->query("
            SELECT
                id, `user`.firstname, `user`.lastname
            FROM
                `membership` LEFT JOIN `user` on `user`.id = membership.user_id
            WHERE membership.group_id = " . $pkId."
            ORDER BY user.lastname ASC"
        )->fetchAll();
        foreach ($data as $item) {
            $result[$item->id] = $item->lastname." ".$item->firstname;
        }
        return $result;
    }
    //vrit
    public function getUsersInPkWholeRow($pkId, $userId)
    {
        $result = array();
        $data = $this->query("
            SELECT
                id, `user`.firstname, `user`.lastname
            FROM
                `membership` LEFT JOIN `user` on `user`.id = membership.user_id
            WHERE
                user_id <> ".$userId." AND membership.group_id = " . $pkId
        )->fetchAll();
        foreach ($data as $item) {
            $result[$item->id] = $item;
        }
        return $result;
    }
    public function getPkIdByLoggedUser($userId)
    {
        $result = null;
        $data = $this->query("
            SELECT
                id
            FROM
                `group`
            WHERE

              user_id =".$userId)->fetchAll(); // ideálně $this->getPresenter()->user->id
        foreach ($data as $item) {
            $result = $item["id"];
        }
        if($result != null)
            return $result;
        return false;
    }
    public function deleteUserFromPk($userId){
        //smaže i záznam o teacher, studetn atd...
        $r = $this->getConnection()->delete($this->getTableName())->where("user_id" . " = " . $userId)->where("group_id > 4")->execute();
        return $r;
    }
    public function getAllUsersExceptForThoseWhoAreInPk($userId)
    {
        $pk = $this->getPkIdByLoggedUser($userId);
        $result = array();
        $data = $this->query("
            SELECT
                child.firstname, child.lastname, child.user_id, child.grp
            FROM
                (
                    (
                        SELECT
                            user.firstname,
                            user.lastname,
                            membership.user_id,
                            MAX(membership.group_id) as grp
                        FROM
                            `membership`
                            LEFT JOIN user on user.id = membership.user_id
                        WHERE
                            group_id <> 3
                            AND group_id <> 4
                            AND group_id <> 1
                        GROUP BY
                            user_id
                    )
                ) as child
            WHERE
                child.grp <> ".$pk."

            ORDER BY
                child.lastname ASC ")->fetchAll(); // ideálně $this->getPresenter()->user->id
        foreach ($data as $item) {
            $result[$item->user_id] = $item->lastname." ".$item->firstname;
        }
        return $result;
    }
    public function insert($data){
        try{
            parent::insert($data);
        }
        catch(\DibiException $e){}
    }
}