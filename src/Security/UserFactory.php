<?php

namespace App\Security;

use App\Entity\SelfcareUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Nbgrp\OneloginSamlBundle\Security\User\SamlUserFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserFactory implements SamlUserFactoryInterface
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createUser(string $identifier, array $attributes): UserInterface
    {
//        dd($attributes);die();
//        $user = $this->em->getRepository(User::class)->findOneBy(['email'=>$identifier]);
//        if ($user){
//            return $user;
//        }
        $user = new SelfcareUser();
        $user->setRoles(['ROLE_STUDENT']);
        $user->setEmail($identifier);
        $user->setPassword(' ');
        $user->setName($attributes['Prénom'][0]);
        $user->setLastName($attributes['Nom'][0]);

//        if (isset($attributes['Matricule'])){
//            $user->setStudentID($attributes['Matricule'][0]);
//        }

//        $this->em->persist($user);
//        $this->em->flush();
//        dd($user);
        return $user;
    }
}