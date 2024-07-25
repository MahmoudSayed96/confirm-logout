<?php

namespace Drupal\confirm_logout\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Confirm Logout settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  public const SETTINGS = 'confirm_logout.settings';

  /**
   * The module handler interface.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token $tokenService
   */
  protected Token $tokenService;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   * @param \Drupal\Core\Utility\Token $tokenService
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $moduleHandler, Token $tokenService) {
    parent::__construct($config_factory);
    $this->moduleHandler = $moduleHandler;
    $this->tokenService = $tokenService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'confirm_logout_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::SETTINGS];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::SETTINGS);
    $form['show_as_popup'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show as popup'),
      '#default_value' => $config->get('show_as_popup') ?? '',
      '#decsription' => $this->t('If checked, the logout form will be shown as a popup.'),
    ];
    $form['popup_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Popup title'),
      '#default_value' => $config->get('popup_title') ?? '',
      '#description' => $this->t('If empty, the title will be "Logout" by default.'),
    ];
    $form['popup_confirm_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Popup confirm text'),
      '#format'        => $config->get('popup_confirm_text') ? $config->get('popup_confirm_text')['format'] : 'basic_html',
      '#default_value' => $config->get('popup_confirm_text') ? $config->get('popup_confirm_text')['value'] : '',
      '#description' => $this->t('If empty, the text will be "Are you sure you want to do logout?" by default.'),
    ];
    $form['popup_cancel_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Popup redirect cancel URL'),
      '#default_value' => $config->get('popup_cancel_url') ?? '',
      '#description' => $this->t('If empty, the URL will be the front page by default.'),
    ];
    $form['popup_confirm_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Popup redirect confirm URL'),
      '#default_value' => $config->get('popup_confirm_url') ?? '',
      '#description' => $this->t('If empty, the URL will be the front page by default.'),
    ];
    // Token support.
    if ($this->moduleHandler->moduleExists('token')) {
      $form['tokens'] = [
        '#title' => $this->t('Tokens'),
        '#type' => 'container',
        '#states' => [
          'invisible' => [
            'input[name="use_token"]' => ['checked' => FALSE],
          ],
        ],
      ];
      $form['tokens']['help'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => [
          'user',
          'site',
        ],
        '#global_types' => FALSE,
        '#dialog' => TRUE,
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(self::SETTINGS)
      ->set('show_as_popup', $form_state->getValue('show_as_popup'))
      ->set('popup_title', $form_state->getValue('popup_title'))
      ->set('popup_confirm_text', $form_state->getValue('popup_confirm_text'))
      ->set('popup_cancel_url', $form_state->getValue('popup_cancel_url'))
      ->set('popup_confirm_url', $form_state->getValue('popup_confirm_url'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
