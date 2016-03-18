<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

namespace Civi\JWSS;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Tokens implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    echo 'hoi';exit();
    return array(
      \Civi\Token\Events::TOKEN_REGISTER => 'register',
    );
  }

  public function register($event) {
    var_dump($event); exit();
  }


}