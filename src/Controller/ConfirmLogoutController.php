<?php

namespace Drupal\confirm_logout\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for Confirm Logout routes.
 */
class ConfirmLogoutController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Render service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected Renderer $render;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Render\Renderer $render
   *   Render service.
   */
  public function __construct(Renderer $render) {
    $this->render = $render;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Redirect to confirm a logout form.
   */
  public function confirmLogout(): RedirectResponse|array {
    $url = Url::fromRoute('confirm_logout.user_confirm_logout')->toString();
    return new RedirectResponse($url);
  }

}
