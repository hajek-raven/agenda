<?php

use Nette\Security\Permission;

class Acl extends Permission
{
    public function __construct(\DibiConnection $db)
    {
        //static roles
        $this->addRole('guest');
        $this->addRole('authenticated','guest');
        $this->addRole('manager','authenticated');
        $this->addRole('administrator','manager');
        $this->addRole('student','authenticated');
        $this->addRole('teacher','authenticated');
        //dynamic roles
        $groups = $db->query("SELECT * FROM `group`")->fetchAll();
        foreach($groups as $group)
        {
            if(!$this->hasRole($group->role_name)) $this->addRole($group->role_name,'authenticated');
        }

        // resources
        $this->addResource('Front:Homepage');
        $this->addResource('Front:Files');
        $this->addResource('Service:Sign');
        $this->addResource('Service:Error');
        $this->addResource('Dashboard:Homepage');
        $this->addResource('Dashboard:Users');
        $this->addResource('Dashboard:Groups');
        $this->addResource('Dashboard:My');
        $this->addResource('Dashboard:Files');
        $this->addResource('Works:Homepage');
        $this->addResource('Works:Sets');
        $this->addResource('Works:Ideas');
        $this->addResource('Works:Assignments');

        // privileges
        $this->allow('guest', array('Front:Homepage','Service:Sign', "Service:Error"), Permission::ALL);
        $this->allow('authenticated', array('Dashboard:My'), Permission::ALL);
        $this->allow('authenticated', array('Dashboard:Groups'), Permission::ALL);
        $this->allow('authenticated', array('Dashboard:Files'), Permission::ALL);
        $this->allow('authenticated', array('Dashboard:Homepage'), array('default'));
        $this->allow('student', array('Works:Homepage'), array('default'));
        $this->allow('teacher', array('Works:Homepage'), Permission::ALL);
        $this->allow('student', array('Works:Ideas'), array('default','add','id','edit','delete','clone'));
        $this->allow('teacher', array('Works:Ideas'), Permission::ALL);
        $this->allow('student', array('Works:Assignments'), array('default','application'));
        $this->allow('teacher', array('Works:Assignments'), array('default','add','id','edit','delete','print'));
        $this->allow('administrator',  Permission::ALL, Permission::ALL);
    }
}
