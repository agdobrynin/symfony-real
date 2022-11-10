<?php
declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Entity\UserPreferences;
use App\Form\RegistrationFormType;
use App\Service\MicroPost\Locales;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validation;
use TypeError;

class RegistrationFormTypeTest extends TypeTestCase
{
    protected const FIELDS_KEY = [
        'login',
        'nick',
        'email',
        'emoji',
        'password',
        'locale',
    ];

    protected const FORM_DATA_GOOD = [
        'login' => 'myLogin',
        'nick' => 'My Nick',
        'email' => 'good@email.com',
        'emoji' => '😀',
        'password' => ['first' => 'myPassword', 'second' => 'myPassword'],
        'locale' => 'en',
    ];

    protected const FORM_DATA_BAD_PASSWORD_NOT_MATCH = [
        'login' => 'myLogin',
        'nick' => 'My Nick',
        'email' => 'good@email.com',
        'emoji' => '😀',
        'password' => ['first' => 'password-one', 'second' => 'password-two'],
        'locale' => 'ru',
    ];

    public function testRegistrationFormTypeEmpty(): void
    {
        $user = new User();
        $form = $this->factory->create(RegistrationFormType::class, $user);
        $form->submit([]);
        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
    }

    public function testRegistrationFormTypeFields(): void
    {
        $form = $this->factory->create(RegistrationFormType::class, new User());
        $fields = $form->createView()->children;
        foreach (self::FIELDS_KEY as $item) {
            self::assertArrayHasKey($item, $fields);
        }
    }

    public function testRegistrationFormTypeSuccess(): void
    {
        $user = new User();
        $form = $this->factory->create(RegistrationFormType::class, $user);
        $form->submit(self::FORM_DATA_GOOD);
        self::assertTrue($form->isSynchronized());
        $user->setPreferences((new UserPreferences())->setLocale($form->get('locale')->getData()));

        self::assertEquals(self::FORM_DATA_GOOD['login'], $user->getLogin());
        self::assertEquals(self::FORM_DATA_GOOD['nick'], $user->getNick());
        self::assertEquals(self::FORM_DATA_GOOD['email'], $user->getEmail());
        self::assertEquals(self::FORM_DATA_GOOD['emoji'], $user->getEmoji());
        self::assertEquals(self::FORM_DATA_GOOD['locale'], $user->getPreferences()->getLocale());

        // Password field is RepeatedType class
        self::assertEquals(self::FORM_DATA_GOOD['password']['first'], $form->get('password')->getData());
        // Password not set because form contain two fields with password - main and repeated.
        self::expectException(TypeError::class);
        $user->getPassword();
    }

    public function testRegistrationFormTypeFailPasswordNotMatch(): void
    {
        $user = new User();
        $form = $this->factory->create(RegistrationFormType::class, $user);
        $form->submit(self::FORM_DATA_BAD_PASSWORD_NOT_MATCH);
        self::assertTrue($form->isSynchronized());

        // Password field is RepeatedType class
        self::assertNull($form->get('password')->getData());
    }

    protected function getExtensions(): array
    {
        $locales = new Locales('ru|en');
        $requestStack = new RequestStack();
        $request = new Request();
        $request->setLocale($locales->getDefaultLocale());
        $requestStack->push($request);

        return [
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension([new RegistrationFormType($locales, $requestStack)], []),
        ];
    }
}
