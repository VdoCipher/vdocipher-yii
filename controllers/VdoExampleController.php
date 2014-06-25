<?php
/**
 * VdoExampleController
 **/
class VdoExampleController extends CController
{
	public function getViewPath()
	{
		return Yii::app()->extensionPath."/vdocipher/views";
	}
	public function actionIndex()
	{
		$dataProvider = Vdo::getDataProvider(array(
			'pagination'=>array(),
			'sort'=>array(),
		));
		$this->renderText("Hello Index", array(
			'dataProvider'=>$dataProvider,
		));
	}

	public function actionDelete($id)
	{
		$vdo = $this->loadModel($id);
		if ($vdo->delete()) {
			$response = "Deleted";
		}
		else
			$response = json_encode($vdo->errors);
		if (isset($_GET['ajax'])){
			echo $response;
			Yii::app()->end();
		}
		$this->setFlash('Video has been deleted');
		$this->redirect('vdoExample/admin');
	}


	public function actionAdmin(){
		$video = new Vdo;
		$this->render('admin', array(
			'video'=>$video
		));
	}

	public function actionView($id)
	{
		$video = $this->loadModel($id);
		$this->render('view', array(
			'video'=>$video,
		));
	}

	public function actionCreate()
	{
		$video = new Vdo;
		$this->render('uploadForm');
	}


	protected function loadModel($id)
	{
		return Vdo::findByPk($id);
	}
}
?>
