<?php

namespace ChrisKonnertz\TranslationFactory\Controllers;

use ChrisKonnertz\TranslationFactory\TranslationFactory;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;

class TranslationFactoryController extends AuthController
{

    /**
     * Index page of the package
     *
     * @param Cache $cache
     * @return \Illuminate\View\View
     */
    public function index(Cache $cache)
    {
        $this->prepare($cache);

        $this->ensureAuth();

        /** @var TranslationFactory $translationFactory */
        $translationFactory = app()->get('translation-factory');

        $reader = $translationFactory->getTranslationReader();
        $translationBags = $reader->readAll();

        $baseLanguage = $this->config->get('app.locale');
        $currentTargetLanguage = $translationFactory->getTargetLanguage();
        $targetLanguages = $translationFactory->getTargetLanguages();

        $data = compact('translationBags', 'baseLanguage', 'currentTargetLanguage', 'targetLanguages');
        return view('translationFactory::home', $data);
    }

    /**
     * This is some kind of setup method. It ensure that the config has been published
     * and that the database has been prepared.
     *
     * @param Cache $cache
     * @throws \Exception
     */
    protected function prepare(Cache $cache)
    {
        if ($this->config->get(TranslationFactory::CONFIG_NAME.'.user_authentication') === null) {
            throw new \Exception(
                'Please publish the assets of the Translation Factory package via: '.
                '"php artisan vendor:publish '.
                '--provider="ChrisKonnertz\TranslationFactory\Integration\TranslationFactoryServiceProvider"'
            );
        }

        // To avoid unnecessary DB checks we set a flag in the cache if we do not need to check the DB
        if (! $cache->has(TranslationFactory::CACHE_KEY.'.db_check')) {
            /** @var TranslationFactory $translationFactory */
            $translationFactory = app()->get('translation-factory');

            $translationFactory->getUserManager()->prepareDatabase();

            $cache->forever(TranslationFactory::CACHE_KEY.'.db_check', time());
        }
    }

    /**
     * Updates the client settings
     *
     * @param Request $request
     * @param Cache   $cache
     * @return \Illuminate\Http\RedirectResponse|null
     * @throws \Exception
     */
    public function update(Request $request, Cache $cache)
    {
        $this->ensureAuth();

        $targetLanguage = $request->input('target_language');

        // Ensure the language code only consists of alphabetical characters
        if (! ctype_alpha($targetLanguage)) {
            throw new \Exception('Error: The given language code is invalid!');
        }

        /** @var TranslationFactory $translationFactory */
        $translationFactory = app()->get('translation-factory');

        if ($this->config->get(TranslationFactory::CONFIG_NAME.'.user_authentication') === true) {
            $cache->forever(TranslationFactory::CACHE_KEY.'.'.$translationFactory->getUserManager()->getCurrentUserId().
                '.target_language', $targetLanguage);
        } else {
            $cache->forever(TranslationFactory::CACHE_KEY.'.target_language', $targetLanguage);
        }

        return null;
    }

    /**
     * Shows a page with the config values of this package
     *
     * @return \Illuminate\View\View
     */
    public function config()
    {
        $this->ensurePermission();

        $configValues = $this->config->get(TranslationFactory::CONFIG_NAME);

        return view('translationFactory::config', compact('configValues'));
    }

    /**
     * Logs the current user out and redirects to website
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        /** @var TranslationFactory $translationFactory */
        $translationFactory = app()->get('translation-factory');

        $translationFactory->getUserManager()->logoutCurrentUser();

        return redirect(url('/'));
    }

}
