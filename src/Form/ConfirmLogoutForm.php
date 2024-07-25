<?php

namespace Drupal\confirm_logout\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form before clearing out the examples.
 */
class ConfirmLogoutForm extends ConfirmFormBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The token manager.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected Token $tokenService;

  /**
   *
   */
  public function __construct(LanguageManagerInterface $languageManager, Token $token) {
    $this->languageManager = $languageManager;
    $this->tokenService = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'confirm_logout_user_confirm_logout';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $confirmText = $this->configFactory()->get(SettingsForm::SETTINGS)->get('popup_confirm_text');
    return !empty($confirmText) ? $confirmText : $this->currentUser()->getAccountName() . ' ' . $this->t('Are you sure you want to do logout?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $options = [
      'language' => $this->languageManager->getCurrentLanguage(),
      'absolute' => FALSE,
    ];
    if ($url = $this->configFactory()->get(SettingsForm::SETTINGS)->get('popup_cancel_url')) {
      $data = [
        'user' => User::load($this->currentUser()->id()),
      ];
      $url = $this->tokenService->replace($url, $data, [
        'clear' => TRUE,
        'langcode' => $this->languageManager->getCurrentLanguage()->getId(),
      ]);
      if ($this->isFullUrl($url)) {
        return Url::fromUri($url, $options);
      }
      return Url::fromUserInput($url, $options);
    }
    return new Url('<front>', [], $options);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $url = $this->configFactory()->get(SettingsForm::SETTINGS)->get('popup_confirm_url');
    $options = [
      'language' => $this->languageManager->getCurrentLanguage(),
      'absolute' => FALSE,
    ];
    $data = [
      'user' => User::load($this->currentUser()->id()),
    ];
    $url = $this->tokenService->replace($url, $data, [
      'clear' => TRUE,
      'langcode' => $this->languageManager->getCurrentLanguage()->getId(),
    ]);
    if ($this->isFullUrl($url)) {
      $url = Url::fromUri($url, $options);
    }
    else {
      $url = !empty($url) ? Url::fromUserInput($url, $options) : new Url('<front>', [], $options);
    }
    if ($this->currentUser()->isAuthenticated()) {
      user_logout();
    }
    $form_state->setRedirectUrl($url);
  }

  /**
   * Get form page title.
   *
   * @return string
   */
  public function getTitle(): string {
    $config = $this->configFactory()->get(SettingsForm::SETTINGS);
    return !empty($config->get('popup_title')) ? $config->get('popup_title') : $this->t('Logout')->render();
  }

  /**
   * Checks if a given string is a full URL.
   *
   * @param string $string
   *   The string to check.
   *
   * @return bool
   *   TRUE if the string is a full URL, FALSE otherwise.
   */
  private function isFullUrl(string $string = ''): bool {
    return filter_var($string, FILTER_VALIDATE_URL);
  }

}
