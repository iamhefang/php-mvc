<?php


namespace link\hefang\mvc\controllers\traits;


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
		return new TemplateView($this->getRouter(), $path, array_merge($data, [
			"config" => [
				"debug" => Mvc::isDebug(),
				"urlPrefix" => Mvc::getUrlPrefix(),
				"fileUrlPrefix" => Mvc::getUrlPrefix(),
				'themeUrl' => "/themes/{$this->getRouter()->getTheme()}",
				"router" => $this->getRouter()->toMap()
			]
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

	public function _redirect(string $url, bool $useJavascript = false): BaseView
	{
		return new RedirectView($url, $useJavascript);
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

	public function _error(int $code, string $message = null): BaseView
	{
		return new ErrorView($code, $message);
	}

	public function _null(): BaseView
	{
		return $this->_text("");
	}

	public function _text(string $text, string $contentType = "text/plain"): BaseView
	{
		return new TextView($text, $contentType);
	}
}
