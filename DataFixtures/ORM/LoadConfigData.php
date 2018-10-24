<?php

namespace FrontBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use LocaleBundle\Entity\Language;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ConfigBundle\Entity\ConfigVariable;

class LoadConfigData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    public $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $languages = $this->container->get('locale.service')->getContentLanguages();
        /**
         * @var $language Language
         */
        foreach ($languages as $language) {
            $variable = new ConfigVariable();
            $variable->setVariable('facebookUrl_' . $language->getLocale())
                ->setType(ConfigVariable::TYPE_STRING)
                ->setValue('#')
                ->setSectionTranslation('front.config.social_settings')
                ->setVariableTranslation('front.config.facebook_url')
                ->setVariableTranslationVariable('{"%locale%": "'.$language->getLocale().'"}')
                ->setScope(ConfigVariable::SCOPE_GLOBAL);
            $manager->persist($variable);

            $variable = new ConfigVariable();
            $variable->setVariable('twitterUrl_' . $language->getLocale())
                ->setType(ConfigVariable::TYPE_STRING)
                ->setValue('#')
                ->setSectionTranslation('front.config.social_settings')
                ->setVariableTranslation('front.config.twitter_url')
                ->setVariableTranslationVariable('{"%locale%": "'.$language->getLocale().'"}')
                ->setScope(ConfigVariable::SCOPE_GLOBAL);
            $manager->persist($variable);

            $variable = new ConfigVariable();
            $variable->setVariable('youtubeUrl_' . $language->getLocale())
                ->setType(ConfigVariable::TYPE_STRING)
                ->setValue('#')
                ->setSectionTranslation('front.config.social_settings')
                ->setVariableTranslation('front.config.youtube_url')
                ->setVariableTranslationVariable('{"%locale%": "'.$language->getLocale().'"}')
                ->setScope(ConfigVariable::SCOPE_GLOBAL);
            $manager->persist($variable);

            $variable = new ConfigVariable();
            $variable->setVariable('googleUrl_' . $language->getLocale())
                ->setType(ConfigVariable::TYPE_STRING)
                ->setValue('#')
                ->setSectionTranslation('front.config.social_settings')
                ->setVariableTranslation('front.config.google_url')
                ->setVariableTranslationVariable('{"%locale%": "'.$language->getLocale().'"}')
                ->setScope(ConfigVariable::SCOPE_GLOBAL);
            $manager->persist($variable);

            $variable = new ConfigVariable();
            $variable->setVariable('telegramUrl_' . $language->getLocale())
                ->setType(ConfigVariable::TYPE_STRING)
                ->setValue('#')
                ->setSectionTranslation('front.config.social_settings')
                ->setVariableTranslation('front.config.telegram_url')
                ->setVariableTranslationVariable('{"%locale%": "'.$language->getLocale().'"}')
                ->setScope(ConfigVariable::SCOPE_GLOBAL);
            $manager->persist($variable);


            $variable = new ConfigVariable();
            $variable->setVariable('whatsappUrl_' . $language->getLocale())
                ->setType(ConfigVariable::TYPE_STRING)
                ->setValue('#')
                ->setSectionTranslation('front.config.social_settings')
                ->setVariableTranslation('front.config.whatsapp_url')
                ->setVariableTranslationVariable('{"%locale%": "'.$language->getLocale().'"}')
                ->setScope(ConfigVariable::SCOPE_GLOBAL);
            $manager->persist($variable);


            $variable = new ConfigVariable();
            $variable->setVariable('mailUrl_' . $language->getLocale())
                ->setType(ConfigVariable::TYPE_STRING)
                ->setValue('#')
                ->setSectionTranslation('front.config.social_settings')
                ->setVariableTranslation('front.config.mail_url')
                ->setVariableTranslationVariable('{"%locale%": "'.$language->getLocale().'"}')
                ->setScope(ConfigVariable::SCOPE_GLOBAL);
            $manager->persist($variable);


            $variable = new ConfigVariable();
            $variable->setVariable('rssUrl_' . $language->getLocale())
                ->setType(ConfigVariable::TYPE_STRING)
                ->setValue('#')
                ->setSectionTranslation('front.config.social_settings')
                ->setVariableTranslation('front.config.rss_url')
                ->setVariableTranslationVariable('{"%locale%": "'.$language->getLocale().'"}')
                ->setScope(ConfigVariable::SCOPE_GLOBAL);
            $manager->persist($variable);
        }

        $variable = new ConfigVariable();
        $variable->setVariable('androidApp')
            ->setType(ConfigVariable::TYPE_STRING)
            ->setValue('#')
            ->setSectionTranslation('front.config.applications')
            ->setVariableTranslation('front.config.android_app_url')
            ->setScope(ConfigVariable::SCOPE_GLOBAL);
        $manager->persist($variable);

        $variable = new ConfigVariable();
        $variable->setVariable('iosApp')
            ->setType(ConfigVariable::TYPE_STRING)
            ->setValue('#')
            ->setSectionTranslation('front.config.applications')
            ->setVariableTranslation('front.config.ios_app_url')
            ->setScope(ConfigVariable::SCOPE_GLOBAL);
        $manager->persist($variable);

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}