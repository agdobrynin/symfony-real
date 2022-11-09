<?php
declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Entity\UserPreferences;
use App\Form\ProfileFormType;
use App\Service\MicroPost\Locales;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class ProfileFormTypeTest extends TypeTestCase
{
    protected const USER_DATA = [
        'emoji' => '😀',
        'email' => 'email@old.com',
        'userLocale' => 'ru'
    ];

    protected const FORM_DATA = [
        'emoji' => '😐',
        'email' => 'email@new.com',
        'userLocale' => 'en'
    ];

    protected function getExtensions(): array
    {
        $locales = new Locales('ru|en');

        return [
            new PreloadedExtension([new ProfileFormType($locales)], []),
        ];
    }

    public function testProfileForm(): void
    {
        $user = (new User())->setEmoji(self::USER_DATA['emoji'])
            ->setEmail(self::USER_DATA['email'])
            ->setPreferences((new UserPreferences())->setLocale(self::USER_DATA['userLocale']));


        $form = $this->factory->create(ProfileFormType::class, $user);

        // preload data to from User entity.
        self::assertEquals(self::USER_DATA['emoji'], $user->getEmoji());
        self::assertEquals(self::USER_DATA['email'], $user->getEmail());
        self::assertEquals(self::USER_DATA['userLocale'], $user->getPreferences()->getLocale());

        $form->submit(self::FORM_DATA);
        self::assertTrue($form->isSynchronized());

        // updated data in User entity after form submit
        $user->getPreferences()->setLocale($form->get('userLocale')->getData());
        self::assertEquals(self::FORM_DATA['emoji'], $user->getEmoji());
        self::assertEquals(self::FORM_DATA['email'], $user->getEmail());
        self::assertEquals(self::FORM_DATA['userLocale'], $user->getPreferences()->getLocale());
    }
}
