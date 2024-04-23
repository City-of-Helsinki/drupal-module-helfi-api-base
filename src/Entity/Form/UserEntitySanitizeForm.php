<?php

namespace Drupal\helfi_api_base\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\helfi_api_base\Entity\Utility\UserEntitySanitizer;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the form for sanitizing chosen user entity.
 */
final class UserEntitySanitizeForm extends FormBase {

  /**
   * UserEntitySanitizeForm constructor.
   *
   * @param \Drupal\helfi_api_base\Entity\Utility\UserEntitySanitizer $sanitizer
   *   UserEntitySanitizer service.
   */
  public function __construct(
    protected UserEntitySanitizer $sanitizer,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('helfi_api_base.user_entity_sanitizer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'user_entity_sanitize_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL): array {

    if (!$user instanceof UserInterface) {
      return ['#markup' => $this->t('User account not found.')];
    }

    if ($user->isActive()) {
      return ['#markup' => $this->t('User account is not deactivated.')];
    }

    $form_state->set('account', $user);
    $form['#title'] = $this->t('Sanitize user account');

    $form['title'] = [
      '#markup' => "<h2>{$this->t('Select the fields to be sanitized for the username %user_name', ['%user_name' => $user->getAccountName()])}</h2>",
    ];

    $form['fields'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Fields'),
      '#options' => [
        'email' => $this->t('Email address'),
        'username' => $this->t('Username'),
        'password' => $this->t('Password'),
      ],
    ];

    $form['confirm'] = [
      '#type' => 'radio',
      '#title' => $this->t('I understand that this action will sanitize all selected data from the user account and the action cannot be undone.'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sanitize'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->get('account')->id()) {
      $this->messenger()->addError($this->t('There was an error with the account.'));
      return;
    }
    $user = $form_state->get('account');

    // Use sanitizer service to Sanitize the user entity fields.
    $operation = $this->sanitizer
      ->sanitizeUserEntity($user, $form_state->getValues()['fields']);

    // If the operation is 0, none of the field values were saved, probably
    // due to a non-existent field selections in the form.
    if ($operation === 0) {
      $this->messenger()->addError($this->t('There was an error with saving the sanitized information to the account.'));
      return;
    }

    // Return to People page after successful sanitization.
    $form_state->setRedirect('entity.user.collection');

    // Add a status message with the uid of the sanitized user.
    $this->messenger()->addStatus($this->t('User account id %user_id was sanitized.', [
      '%user_id' => $user->id(),
    ]));
  }

}
