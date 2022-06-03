<?php

namespace nstcactus\CraftUtils;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterEmailMessagesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\i18n\PhpMessageSource;
use craft\services\Elements;
use craft\services\SystemMessages;
use craft\services\UserPermissions;
use craft\web\twig\variables\Cp;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use yii\base\Event;
use yii\base\Module;

/**
 * This base class for custom modules aims at making less painful to create a Craft module.
 * Most of the time, all you have to do to register/customize the following components is to override the corresponding
 * getter:
 *   - translation cateory (resonnable default value provided)
 *   - CP template roots (resonnable default value provided)
 *   - site template roots (resonnable default value provided)
 *   - twig extensions
 *   - CP nav items
 *   - CP routes
 *   - site routes
 *   - User permissions
 *   - Craft variables additions
 *   - element types
 *   - view hooks
 */
class AbstractModule extends Module
{
    public function __construct($id, $parent = null, $config = [])
    {
        $this->id = $id;
        $this->registerAliases();
        $this->setControllerNamespace();
        $this->registerTranslationCategory();
        $this->registerCpTemplateRoots();
        $this->registerSiteTemplateRoots();
        self::setInstance($this);

        parent::__construct($id, $parent, $config);
    }

    public function init()
    {
        parent::init();

        $this->registerTwigExtensions();
        $this->registerCpNavItems();
        $this->registerCpRoutes();
        $this->registerSiteRoutes();
        $this->registerUserPermissions();
        $this->registerVariables();
        $this->registerElementTypes();
        $this->registerViewHooks();
    }

    /**
     * Register a translation category for the module.
     * @see \nstcactus\CraftUtils\AbstractModule::getTranslationCategory()
     */
    protected function registerTranslationCategory(): void
    {
        $translationCategory = $this->getTranslationCategory();

        $i18n = Craft::$app->getI18n();
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($i18n->translations[$translationCategory]) && !isset($i18n->translations[$translationCategory . '*'])) {
            $i18n->translations[$translationCategory] = [
                'class'            => PhpMessageSource::class,
                'sourceLanguage'   => 'en',
                'basePath'         => Craft::getAlias("@modules/$this->id/translations"),
                'forceTranslation' => true,
                'allowOverrides'   => true,
            ];
        }
    }

    /**
     * Register template roots for the control panel
     * @see \nstcactus\CraftUtils\AbstractModule::getCpTemplateRoots()
     */
    protected function registerCpTemplateRoots(): void
    {
        Event::on(View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $e) {
                $e->roots = array_merge($e->roots, $this->getCpTemplateRoots());
            });
    }

    /**
     * Register template roots for the site
     * @see \nstcactus\CraftUtils\AbstractModule::getSiteTemplateRoots()
     */
    protected function registerSiteTemplateRoots(): void
    {
        Event::on(View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $e) {
                $e->roots = array_merge($e->roots, $this->getSiteTemplateRoots());
            });
    }

    /**
     * Set the controller namespace according to the type of request (console or web).
     * The default implementation expects controller to reside in the following namespaces:
     *   - `<moduleNamespace>\console\controllers` for console requests
     *   - `<moduleNamespace>\controllers` for web requests
     */
    protected function setControllerNamespace(): void
    {
        $fqcn = get_class($this);
        $namespace = substr($fqcn, 0, strrpos($fqcn, '\\'));

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = "$namespace\console\controllers";
        } else {
            $this->controllerNamespace = "$namespace\controllers";
        }
    }

    /**
     * Return the translation category.
     * The default value is the module handle specified in app.php. Feel free to override it if it suits you.
     * @return string
     */
    protected function getTranslationCategory(): string
    {
        return $this->id;
    }

    /**
     * Register CP routes.
     * The default implementation simply adds the routes returned by self::getCpRoutes().
     */
    protected function registerCpRoutes(): void
    {
        $cpRoutes = $this->getCpRoutes();

        if ($cpRoutes) {
            Event::on(
                UrlManager::class,
                UrlManager::EVENT_REGISTER_CP_URL_RULES,
                function (RegisterUrlRulesEvent $e) use ($cpRoutes) {
                    $e->rules = array_merge($e->rules, $cpRoutes);
                }
            );
        }
    }

    /**
     * Register site routes.
     * The default implementation simply adds the routes returned by self::getCpRoutes().
     */
    protected function registerSiteRoutes(): void
    {
        $siteRoutes = $this->getSiteRoutes();

        if ($siteRoutes) {
            Event::on(
                UrlManager::class,
                UrlManager::EVENT_REGISTER_SITE_URL_RULES,
                function (RegisterUrlRulesEvent $e) use ($siteRoutes) {
                    $e->rules = array_merge($e->rules, $siteRoutes);
                }
            );
        }
    }

    /**
     * Register Twig extensions
     * @see \nstcactus\CraftUtils\AbstractModule::getTwigExtensions()
     */
    protected function registerTwigExtensions(): void
    {
         $twigExtensions = $this->getTwigExtensions();

        if ($twigExtensions) {
            foreach ($twigExtensions as $twigExtension) {
                Craft::$app->view->registerTwigExtension($twigExtension);
            }
        }
    }

    /**
     * Register CP nav items
     * @see \nstcactus\CraftUtils\AbstractModule::getCpNavItems()
     */
    protected function registerCpNavItems(): void
    {
        $cpNavItems = $this->getCpNavItems();
        if ($cpNavItems) {
            Event::on(
                Cp::class,
                Cp::EVENT_REGISTER_CP_NAV_ITEMS,
                function (RegisterCpNavItemsEvent $e) use ($cpNavItems) {
                    $e->navItems = array_merge($e->navItems, $cpNavItems);
                }
            );
        }
    }

    /**
     * Register user permissions
     * @see \nstcactus\CraftUtils\AbstractModule::getUserPermissions()
     */
    protected function registerUserPermissions(): void
    {
        $permissions = $this->getUserPermissions();

        if ($permissions) {
            Event::on(
                UserPermissions::class,
                UserPermissions::EVENT_REGISTER_PERMISSIONS,
                function (RegisterUserPermissionsEvent $event) use ($permissions) {
                    $event->permissions = array_merge_recursive($event->permissions, $permissions);
                }
            );
        }
    }

    /**
     * Register Craft variable additions
     * @see \nstcactus\CraftUtils\AbstractModule::getVariables()
     */
    protected function registerVariables(): void
    {
        $variables = $this->getVariables();
        if ($variables) {
            Event::on(
                CraftVariable::class,
                CraftVariable::EVENT_INIT,
                function (Event $event) use ($variables) {
                    foreach ($variables as $name => $variable) {
                        $event->sender->set($name, $variable);
                    }
                }
            );
        }
    }


    /**
     * Register element types
     * @see \nstcactus\CraftUtils\AbstractModule::getElementTypes()
     */
    protected function registerElementTypes(): void
    {
        $elementTypes = $this->getElementTypes();
        if ($elementTypes) {
            Event::on(
                Elements::class,
                Elements::EVENT_REGISTER_ELEMENT_TYPES,
                function(RegisterComponentTypesEvent $event) use ($elementTypes) {
                    $event->types = array_merge($event->types, $elementTypes);
                }
            );
        }
    }

    /**
     * Register Yii aliases.
     * The default implementation adds an `@modules/<handle>` & a `@<namespace>` alias.
     * Example: For a module having the handle `custom-mobule` in the `\modules\lhs\customModule` namespace, the following
     * aliases will be added: `@modules/custom-module` & `@modules/lhs/customModule`
     */
    protected function registerAliases(): void
    {
        $childClassNamespace = dirname(str_replace('\\', '/', get_class($this)));
        Craft::setAlias("modules/$this->id", $this->getBasePath());
        Craft::setAlias($childClassNamespace, $this->getBasePath());
    }

    /**
     * Register view hooks
     * @see \nstcactus\CraftUtils\AbstractModule::getViewHooks()
     */
    protected function registerViewHooks(): void
    {
        $hooks = $this->getViewHooks();

        $view = Craft::$app->view;
        foreach ($hooks as $name => $handler)
        {
            $view->hook($name, $handler);
        }
    }

    /**
     * Return the routes to be added the default Craft CP routes
     * @return ?array An associative array mapping url rules to the module/controller/action that should handle it
     */
    protected function getCpRoutes(): ?array
    {
        return null;
    }

    /**
     * Return the routes to be added the default Craft site routes
     * @return ?array An associative array mapping url rules to the module/controller/action that should handle it
     */
    protected function getSiteRoutes(): ?array
    {
        return null;
    }

    /**
     * Return the additional Twig extensions to load
     * @return ?array An (indexed) array of Twig extension instances
     */
    protected function getTwigExtensions(): ?array
    {
        return null;
    }

    /**
     * Return the CP nav items to register
     * @return ?array An array of CP nav items
     * @see https://craftcms.com/docs/3.x/extend/cp-section.html for details on the syntax of the array
     */
    protected function getCpNavItems(): ?array
    {
        return null;
    }

    /**
     * Return the user permissions to register
     * @return ?array An array of user permissions
     * @see https://craftcms.com/docs/3.x/extend/user-permissions.html for details on the syntax of the array
     */
    protected function getUserPermissions(): ?array
    {
        return null;
    }

    /**
     * Return the variables to register
     * @return ?array An associative associative array mapping the variable names to their corresponding class names or instances
     * @example [ 'breadcrumb' => \nstcactus\CraftUtils\breadcrumb\variables\SeoVariable::class ]
     */
    protected function getVariables(): ?array
    {
        return null;
    }

    /**
     * Return an array of elements types to register
     * @return ?array An array of element type FQCN
     */
    protected function getElementTypes(): ?array
    {
        return null;
    }

    /**
     * Return an associative array of CP template roots.
     * @see https://craftcms.com/docs/3.x/extend/template-roots.html
     * @return ?array
     */
    protected function getCpTemplateRoots(): ?array
    {
        $templateRoots = [];
        if (is_dir($baseDir = Craft::getAlias("@modules/$this->id/templates/cp"))) {
            $templateRoots[$this->id] = $baseDir;
        }

        return $templateRoots;
    }

    /**
     * Return an associative array of site template roots.
     * @see https://craftcms.com/docs/3.x/extend/template-roots.html
     * @return ?array
     */
    protected function getSiteTemplateRoots(): ?array
    {
        $templateRoots = [];
        if (is_dir($baseDir = Craft::getAlias("@modules/$this->id/templates/site"))) {
            $templateRoots[$this->id] = $baseDir;
        }

        return $templateRoots;
    }

    /**
     * Return an associative array of view hooks (where the key is the name of the hook and the value is a callback to
     * execute when this hook is called in the view).
     * @return array
     * @example [ 'custom-hook' => function(array &context, bool &handled) { return 'This will be echoed in the template'; }]
     * @see craft\web\View::hook()
     */
    protected function getViewHooks(): array
    {
        return [];
    }


    /**
     * Register system messages
     * @see \nstcactus\CraftUtils\AbstractModule::getSystemMessages()
     */
    protected function registerSystemMessages(): void
    {
        $messages = $this->getSystemMessages();
        if ($messages) {
            Event::on(SystemMessages::class, SystemMessages::EVENT_REGISTER_MESSAGES, static function (RegisterEmailMessagesEvent $event) use ($messages) {
                foreach ($messages as $message) {
                    $event->messages[] = $message;
                }
            });
        }
    }

    /**
     * Return an array of system messages.
     * Each element must be either a `\craft\models\SystemMessage` instance or an associative arrays with the following keys:
     *   - `key`
     *   - `heading`
     *   - `subject`
     *   - `body`
     * @return ?array
     * @see \craft\events\RegisterEmailMessagesEvent
     */
    protected function getSystemMessages(): ?array
    {
        return null;
    }
}
