<?php
namespace Opencart\Admin\Controller\Extension\Ovesio\Module;

class Ecommerce extends \Opencart\System\Engine\Controller {
	private $error = array();

	public function index(): void {
		$this->load->language('extension/ovesio/module/ecommerce');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ovesio/module/ecommerce', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['save'] = $this->url->link('extension/ovesio/module/ecommerce.save', 'user_token=' . $this->session->data['user_token'], true);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_ovesio_ecommerce_status'])) {
			$data['module_ovesio_ecommerce_status'] = $this->request->post['module_ovesio_ecommerce_status'];
		} else {
			$data['module_ovesio_ecommerce_status'] = $this->config->get('module_ovesio_ecommerce_status');
		}

		if (isset($this->request->post['module_ovesio_ecommerce_export_duration'])) {
			$data['module_ovesio_ecommerce_export_duration'] = $this->request->post['module_ovesio_ecommerce_export_duration'];
		} else {
			$data['module_ovesio_ecommerce_export_duration'] = $this->config->get('module_ovesio_ecommerce_export_duration');
		}

		// If hash is missing (e.g. manual update), generate it
		if(isset($this->request->post['module_ovesio_ecommerce_hash'])) {
			$hash = $this->request->post['module_ovesio_ecommerce_hash'];
		} elseif ($this->config->get('module_ovesio_ecommerce_hash')) {
			$hash = $this->config->get('module_ovesio_ecommerce_hash');
		} else {
			$hash = md5(uniqid(mt_rand(), true));
		}

		$data['module_ovesio_ecommerce_hash'] = $hash;

		// Generate URLs
		$data['product_feed_url'] = HTTP_CATALOG . 'index.php?route=extension/ovesio/module/ecommerce&hash=' . $hash;
		$data['order_feed_url'] = HTTP_CATALOG . 'index.php?route=extension/ovesio/module/ecommerce&action=orders&hash=' . $hash;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/ovesio/module/ecommerce', $data));
	}

	public function save(): void {
		$this->load->language('extension/ovesio/module/ecommerce');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('module_ovesio_ecommerce', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			// Check if using AJAX (OC4.0.2+)
			if (isset($this->request->post['json'])) {
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode(['success' => $this->language->get('text_success')]));
			} else {
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			}
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/ovesio/module/ecommerce')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install(): void {
		$this->load->model('setting/setting');

		$hash = md5(uniqid(mt_rand(), true));
		$settings = array(
			'module_ovesio_ecommerce_status' => 0,
			'module_ovesio_ecommerce_export_duration' => 12,
			'module_ovesio_ecommerce_hash' => $hash
		);

		$this->model_setting_setting->editSetting('module_ovesio_ecommerce', $settings);
	}

	public function uninstall(): void {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('module_ovesio_ecommerce');
	}
}
