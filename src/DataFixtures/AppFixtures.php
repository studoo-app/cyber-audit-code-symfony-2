<?php

namespace App\DataFixtures;

use App\Entity\Entry;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{


    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $this->loadUsers($manager,$faker);
        $this->loadEntries($manager,$faker);
    }

    private function loadUsers(ObjectManager $manager, Generator $faker): void
    {

        for ($i = 0; $i < 5; ++$i) {
            $user = new User();
            if($i === 0){
                $user->setEmail("admin@msv.dev");
                $user->setPassword($this->hasher->hashPassword($user,"password"));
                $user->setRoles(["ROLE_ADMIN"]);
            }else{
                $user->setEmail($faker->safeEmail);
                $user->setPassword($this->hasher->hashPassword($user,$faker->password(6,12)));
                $user->setRoles(["ROLE_USER"]);
            }

            $manager->persist($user);
        }

        $manager->flush();

    }

    private function loadEntries(ObjectManager $manager, Generator $faker): void
    {
        $users = array_filter(
            $manager->getRepository(User::class)->findAll(),
            function($user){
                if(in_array("ROLE_USER",$user->getRoles())){
                    return $user;
                }
            }
        );

        foreach ($users as $user){
            $tags = $this->generateTags($manager,$faker,$user);
            for($i=0;$i<random_int(3,7);$i++){

                $tagKey = array_rand($tags,1);
                $entry = new Entry();
                $entry->setUser($user);
                $entry->setLogin($faker->userName());
                $entry->setPassword($faker->password(6, 12));

                $url = explode("/",$faker->url());
                $title = $url[2];
                $url = implode('/',[$title,random_int(0,1) ? "login" : "auth"]) ;


                $entry->setUrl($url);

                $entry->setTitle($title);
                $entry->setTag($tags[$tagKey]);

                $manager->persist($entry);
            }
        }

        $manager->flush();

    }

    private function generateTags(ObjectManager $manager, Generator $faker,User $user): array
    {
        $tags = [null];
        for($i=0;$i<random_int(1,3);$i++){
            $tag = new Tag(ucfirst($faker->word),$user);
            $manager->persist($tag);
            $tags[] = $tag;
        }

        return $tags;
    }
}
