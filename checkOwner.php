<?php

namespace andreykluev\checkowner;

use Yii;
use yii\base\Behavior;
use yii\web\HttpException;
use yii\web\Controller;

/**
 * Class checkOwner
 * @package common\behaviors
 */
class checkOwner extends Behavior
{
    public $actions = [];

    /**
     * Навешиваем проверку на событие
     * @return array
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'checkIsOwner'
        ];
    }

    /**
     * Проверяем существование модели и ее владельца
     * @throws HttpException
     */
    public function checkIsOwner()
    {
        // Если нужно проверить
        if (isset($this->actions[$this->owner->action->id])) {
            $modelClass = $this->actions[$this->owner->action->id][0];
            $ownerField = $this->actions[$this->owner->action->id][1];

            // Если не передан id, генерим Exception
            $id = Yii::$app->request->get('id', 0);
            if ($id === 0) {
                throw new HttpException(404, 'Expected get parameter id.');
            }

            // Определяем модель в соответствии с переданным именем класса
            $model = call_user_func([$modelClass, 'findOne'], [$id]);

            // Если модель не найдена, генерим Exception
            if ($model === null) {
                throw new HttpException(404, 'Model not found');
            }

            // Проверяем владельца
            if ($model[$ownerField] !== Yii::$app->getUser()->getId()) {
                throw new HttpException(403, 'Access denied');
            }
        }
    }
}