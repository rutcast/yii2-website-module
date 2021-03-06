<?php

namespace daxslab\website\controllers\backend;

use daxslab\website\models\Media;
use daxslab\website\models\MediaSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Controller;
use yii\imagine\Image as Imagine;

class MediaController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Content models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MediaSearch([
            'website_id' => Yii::$app->website->id,
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort->defaultOrder = [
            'created_at' => SORT_DESC,
        ];
        $dataProvider->pagination->route = Url::toRoute(['/website/media/index']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Uploads a single Content model.
     * @return mixed
     */
    public function actionUpload()
    {
        $model = new Media([
            'website_id' => Yii::$app->website->id,
        ]);

        $success = $model->save();
        if ($success) {
            if($model->isImage){
                Imagine::resize($model->path, 1500, 1500, true, true)
                    ->save($model->path, ['quality' => 75]);
            }
        } else {
            $errors = $model->errors;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'success' => $success,
            'errors' => isset($errors) ? $errors : [],
        ];
    }

    /**
     * @param $id
     * @return mixed
     * @throws \yii\web\ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDownload($id)
    {
        $model = $this->findModel($id);

        if (!file_exists($model->path)) {
            throw new NotFoundHttpException(Yii::t('website','File not found'));
        }

        return Yii::$app->response->sendFile($model->path, $model->prettyName);
    }

    /**
     * @return string
     */
    public function actionImagesGallery()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Yii::$app->website->getImages()->orderBy('filename'),
            'pagination' => false,
        ]);

        return $this->renderPartial('images-gallery', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGetImagesForGallery()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $images = Yii::$app->website->getImages()->orderBy('filename')->all();

        $imagesList = [];

        $thumbnailer = Yii::$app->thumbnailer;
//        $website = str_replace(['http://', 'https://'], ['', ''], $this->currentWebsite->url);
//        $thumbnailer->thumbnailsPath = Yii::getAlias("@webroot/../../websites/{$website}/web/assets/thumbnails");
//        $thumbnailer->thumbnailsBaseUrl = "{$this->currentWebsite->url}/assets/thumbnails";

        foreach ($images as $image) {
            $img = [
                "thumb" => $thumbnailer->get($image->url, 100, 100),
                "image" => $image->url,
                "folder" => Yii::t('website','Gallery'),
            ];
            $imagesList[] = $img;
        }

        return $imagesList;

    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        return $this->redirect(["/{$this->module->id}/media/index"]);
    }

    /**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Media the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Media::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('website','The requested page does not exist.'));
    }

}
