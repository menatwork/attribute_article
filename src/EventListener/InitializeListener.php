<?php
/**
 * Created by PhpStorm.
 * User: andreas.dziemba
 * Date: 03.08.2018
 * Time: 13:49
 */

namespace MetaModels\AttributeArticleBundle\EventListener;

use Contao\Input;
use Contao\CoreBundle\Routing\ScopeMatcher;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InitializeListener
{
    /**
     * Constructor.
     *
     * @param TokenStorageInterface                $tokenStorage                The token storage.
     * @param AuthenticationTrustResolverInterface $authenticationTrustResolver The authentication resolver.
     * @param ScopeMatcher                         $scopeMatcher                The scope matche.
     * @param ViewCombination                      $viewCombination             The view combination.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationTrustResolverInterface $authenticationTrustResolver,
        ScopeMatcher $scopeMatcher,
        ViewCombination $viewCombination
    ) {
        $this->tokenStorage                = $tokenStorage;
        $this->authenticationTrustResolver = $authenticationTrustResolver;
        $this->scopeMatcher                = $scopeMatcher;
        $this->viewCombination             = $viewCombination;
    }

    /**
     * Replaces the current session data with the stored session data.
     *
     * @param GetResponseEvent $event The event.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$this->scopeMatcher->isBackendMasterRequest($event)) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token || $this->authenticationTrustResolver->isAnonymous($token)) {
            return;
        }

        $localMenu = &$GLOBALS['BE_MOD'];
        $this->clearBackendModules($localMenu);
    }

    /**
     * Add the modules to the backend sections.
     *
     * @param array $localMenu Reference to the global array.
     *
     * @return void
     */
    public function clearBackendModules(&$localMenu)
    {

        $strModule = Input::get('do');
        $strTable  = Input::get('table');

        if (substr($strModule, 0, 10) == 'metamodel_' && $strTable == 'tl_content') {
            $GLOBALS['BE_MOD']['content'][$strModule]['tables'][] = 'tl_content';
            $GLOBALS['BE_MOD']['content'][$strModule]['callback'] = null;
            $GLOBALS['BE_MOD']['content'][$strModule]['addMainLangContent'] = ['MetaModels\\AttributeArticleBundle\\Table\\MetaModelAttributeArticle', 'addMainLangContent'];
        }
    }
}