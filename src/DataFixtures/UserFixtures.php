<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
     private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Бумага', 'Канцелярия', 'Игрушки', 'Отдых'
        ];

        // Create general user
        $user = new User();
        $user->setEmail('test@gmail.com');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'test_user'
        ));
        $user->setRoles(["ROLE_USER"]);
        $user->setCompanyName('user_company');
        $manager->persist($user);

        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setPurchaseUser($user);
            $category->setName($categoryName);
            $manager->persist($category);
        }

        // Create super man
        $user = new User();
        $user->setEmail('admin@gmail.com');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'super_admin'
        ));
        $user->setRoles(["ROLE_SUPER_ADMIN", "ROLE_USER"]);
        $user->setCompanyName('admin_company');
        $manager->persist($user);

        $manager->flush();
    }
}
