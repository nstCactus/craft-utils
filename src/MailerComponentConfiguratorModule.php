<?php
namespace nstcactus\CraftUtils;

use Craft;
use craft\errors\MissingComponentException;
use craft\helpers\App;
use Craft\mail\Mailer;
use craft\mail\transportadapters\Smtp;
use craft\models\MailSettings;
use yii\base\Module;

class MailerComponentConfiguratorModule extends AbstractModule
{
    protected const ADAPTER_CLASSNAME_MAILJET = '\\bertoost\\mailjet\\adapters\\MailjetAdapter';
    protected const ADAPTER_CLASSNAME_MAILCHIMP = '\\perfectwebteam\\mailchimptransactional\\mail\\MailchimpTransactionalAdapter';

    public function init(): void
    {
        parent::init();

        switch (App::env('NST_MAILER_TRANSPORT_TYPE')) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'mandrill':
                Craft::$app->getDeprecator()->log(
                    'mailer-component-configurator-module-mandrill-adaptor-slug-deprecated',
                    'The `mandrill` value is deprecated for the `NST_MAILER_TRANSPORT_TYPE` environment variable. Use `mailchimp` instead.'
                );
            case 'mailchimp':
            case self::ADAPTER_CLASSNAME_MAILCHIMP:
                $mailerConfigFunction = 'getMailchimpConfiguration';
                break;

            case 'mailjet':
            case self::ADAPTER_CLASSNAME_MAILJET:
                $mailerConfigFunction = 'getMailjetConfiguration';
                break;

            case 'smtp':
            case Smtp::class:
                $mailerConfigFunction = 'getSmtpConfiguration';
                break;
        }

        if (isset($mailerConfigFunction)) {
            Craft::$app->set('mailer', function() use ($mailerConfigFunction) {
                return $this->$mailerConfigFunction();
            });
        }
    }

    protected function getMailchimpConfiguration(): Mailer
    {
        if (!class_exists(self::ADAPTER_CLASSNAME_MAILCHIMP)) {
            throw new MissingComponentException('The `mailchimp-transactional` plugin must be installed to use the mailchimp adapter');
        }

        // Get the stored email settings
        $settings = $this->getDefaultMailerSettings();
        $settings->transportType = self::ADAPTER_CLASSNAME_MAILCHIMP;
        $settings->transportSettings = [
            'apiKey' => App::env('NST_MAILER_MAILCHIMP_API_KEY'),
            'subaccount' => App::env('NST_MAILER_MAILCHIMP_SUBACCOUNT') ?: null,
            'template' => App::env('NST_MAILER_MAILCHIMP_TEMPLATE') ?: null,
        ];

        $config = App::mailerConfig($settings);

        return Craft::createObject($config);
    }

    protected function getMailjetConfiguration(): Mailer
    {
        if (!class_exists(self::ADAPTER_CLASSNAME_MAILJET)) {
            throw new MissingComponentException('The `mailjet` plugin must be installed to use the mailjet adapter');
        }

        // Get the stored email settings
        $settings = $this->getDefaultMailerSettings();
        $settings->transportType = self::ADAPTER_CLASSNAME_MAILJET;
        $settings->transportSettings = [
            'apiKey'    => App::env('NST_MAILER_MAILJET_API_KEY'),
            'apiSecret' => App::env('NST_MAILER_MAILJET_API_SECRET'),
        ];

        $config = App::mailerConfig($settings);

        return Craft::createObject($config);
    }

    protected function getSmtpConfiguration(): Mailer
    {
        $settings = $this->getDefaultMailerSettings();
        $settings->transportType = Smtp::class;
        $settings->transportSettings = [
            'host'              => App::env('NST_MAILER_SMTP_HOST'),
            'port'              => App::env('NST_MAILER_SMTP_PORT'),
            'username'          => App::env('NST_MAILER_SMTP_USERNAME'),
            'password'          => App::env('NST_MAILER_SMTP_PASSWORD'),
            'useAuthentication' => App::parseBooleanEnv('$NST_MAILER_SMTP_USE_AUTHENTICATION') ?? false,
            'encryptionMethod'  => App::env('EMAIL_SMTP_ENCRYPTION') ?: null,
        ];

        $config = App::mailerConfig($settings);

        return Craft::createObject($config);
    }

    /**
     * @return MailSettings
     */
    protected function getDefaultMailerSettings(): MailSettings
    {
        $settings = App::mailSettings();
        $settings->fromEmail = App::env('NST_MAILER_FROM_EMAIL') ?: 'tech@lahautesociete.com';
        $settings->fromName = App::env('NST_MAILER_FROM_NAME') ?: null;

        return $settings;
    }
}
