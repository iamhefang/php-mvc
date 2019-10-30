<?php


namespace link\hefang\mvc\controllers\traits;


use link\hefang\helpers\StringHelper;
use link\hefang\mvc\controllers\BaseController;
use link\hefang\mvc\exceptions\TraitException;
use link\hefang\mvc\Mvc;
use link\hefang\mvc\views\BaseView;
use link\hefang\mvc\views\ErrorView;
use link\hefang\mvc\views\FileView;
use link\hefang\mvc\views\ImageView;
use link\hefang\mvc\views\RedirectView;
use link\hefang\mvc\views\TemplateView;
use link\hefang\mvc\views\TextView;

trait NotApiControllerTrait
{
//	public function __construct()
//	{
//		if (!is_subclass_of($this, BaseController::class)) {
//			throw new TraitException("无法在类\"" . __CLASS__ . "\"中使用\"" . NotApiControllerTrait::class . "\"");
//		}
//	}

	/**
	 * 生成一个模板视图
	 * @param array|null $data
	 * @param string|null $path
	 * @return BaseView
	 */
	public function _template(array $data = null, string $path = null): BaseView
	{
		$theme = $this->getRouter()->getTheme();
		$controller = $this->getRouter()->getController();
		$module = $this->getRouter()->getModule();
		$action = $this->getRouter()->getAction();
		$ds = DIRECTORY_SEPARATOR;
		if (StringHelper::isNullOrBlank($path)) {
			if ($module === Mvc::getDefaultModule() && $controller === Mvc::getDefaultController()) {
				$path = $ds . join($ds, [$theme, $action]);
			} else {
				$path = $ds . join($ds, [$theme, $module, $controller, $action]);
			}
		}
		$path = str_replace("/", $ds, $path);
		if (strpos($path, $ds) === false) {
			$path = $ds . join($ds, [
					$this->getRouter()->getTheme(),
					$path
				]);
		} elseif ($path{0} !== $ds) {
			$path = $ds . $this->getRouter()->getTheme() . $ds . $path;
		}

		if (!StringHelper::endsWith($path, true, ".php")) {
			$path = $path . ".php";
		}
		return new TemplateView($path, array_merge($data, [
			'themeUrl' => "/themes/{$this->getRouter()->getTheme()}"
		]));
	}

	/**
	 * 把绘制的图像返回到前端
	 * @param resource $img 绘制的图像资源
	 * @param string $imageType 文件类型
	 * @return BaseView
	 */
	public function _image($img, string $imageType = 'image/png'): BaseView
	{
		return new ImageView($img, $imageType);
	}

	public function _text(string $text, string $contentType = "text/plain"): BaseView
	{
		return new TextView($text, $contentType);
	}

	public function _redirect(string $url, bool $useJavascript = false): BaseView
	{
		return new RedirectView($url, $useJavascript);
	}

	public function _error(int $code, string $message = null): BaseView
	{
		return new ErrorView($code, $message);
	}

	/**
	 * @param string $filename
	 * @param string|null $mimeType
	 * @return BaseView
	 */
	public function _file(string $filename, string $mimeType = null): BaseView
	{
		return new FileView($filename, $mimeType);
	}

	public function _404(): BaseView
	{
		return $this->_error(404, "Not Found");
	}

	public function _null(): BaseView
	{
		return $this->_text("");
	}
}
