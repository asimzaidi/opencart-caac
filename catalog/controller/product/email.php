<?php 
class ControllerProductEmail extends Controller {
    private $error = array(); 
        
      public function index() {
        $this->language->load('information/contact');

        $this->document->setTitle($this->language->get('heading_title1'));  
     
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->hostname = $this->config->get('config_smtp_host');
            $mail->username = $this->config->get('config_smtp_username');
            $mail->password = $this->config->get('config_smtp_password');
            $mail->port = $this->config->get('config_smtp_port');
            $mail->timeout = $this->config->get('config_smtp_timeout');                
            $mail->setTo($this->language->get('to_email'));
            $mail->setFrom($this->request->post['email']);
            $mail->setSender($this->request->post['name']);
            $mail->setSubject(html_entity_decode(sprintf($this->request->post['subject'], $this->request->post['name']), ENT_QUOTES, 'UTF-8'));
            $mail->setText(strip_tags(html_entity_decode($this->request->post['enquiry'], ENT_QUOTES, 'UTF-8')));
            $mail->send();
            $this->session->data['success'] = $this->language->get('text_success');
            $this->redirect($this->url->link('product/product&product_id='.$this->request->get['product_id']));
        }
        
        $this->sendEmail();
        
          $this->data['breadcrumbs'] = array();

          $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),            
            'separator' => false
          );

          $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_email'),
            'href'      => $this->url->link('product/email'),
            'separator' => $this->language->get('text_separator')
          );    
            
        $this->data['text_heading'] = $this->language->get('text_heading');
        $this->data['entry_name'] = $this->language->get('entry_name');
        $this->data['entry_email'] = $this->language->get('entry_email');
        $this->data['entry_enquiry'] = $this->language->get('entry_enquiry_product');
        $this->data['entry_captcha'] = $this->language->get('entry_captcha');
        
        $this->data['column_image'] = $this->language->get('column_image');
        $this->data['column_name'] = $this->language->get('column_name');
        $this->data['column_model'] = $this->language->get('column_model');
        $this->data['column_quantity'] = $this->language->get('column_quantity');
        $this->data['column_price'] = $this->language->get('column_price');
        $this->data['column_total'] = $this->language->get('column_total');
        $this->data['entry_subject'] = $this->language->get('entry_subject');

        if (isset($this->error['name'])) {
            $this->data['error_name'] = $this->error['name'];
        } else {
            $this->data['error_name'] = '';
        }
        
        if (isset($this->error['email'])) {
            $this->data['error_email'] = $this->error['email'];
        } else {
            $this->data['error_email'] = '';
        }        
        
        if (isset($this->error['enquiry'])) {
            $this->data['error_enquiry'] = $this->error['enquiry'];
        } else {
            $this->data['error_enquiry'] = '';
        }        
        
         if (isset($this->error['captcha'])) {
            $this->data['error_captcha'] = $this->error['captcha'];
        } else {
            $this->data['error_captcha'] = '';
        }                           
        
        if (isset($this->error['option'])) {
            $this->data['error_option'] = $this->error['option'];
        } else {
            $this->data['error_option'] = '';
        }    

        $this->data['button_continue'] = $this->language->get('button_continue');
    
        $this->data['action'] = $this->url->link('product/email&product_id='.$this->request->get['product_id']);
        $this->data['store'] = $this->config->get('config_name');
        
        if (isset($this->request->post['name'])) {
            $this->data['name'] = $this->request->post['name'];
        } else {
            $this->data['name'] = $this->customer->getFirstName();
        }

        if (isset($this->request->post['email'])) {
            $this->data['email'] = $this->request->post['email'];
        } else {
            $this->data['email'] = $this->customer->getEmail();
        }
        
        if (isset($this->request->post['enquiry'])) {
            $this->data['enquiry'] = $this->request->post['enquiry'];
        } else {
            $this->data['enquiry'] = '';
        }
        
        if (isset($this->request->post['captcha'])) {
            $this->data['captcha'] = $this->request->post['captcha'];
        } else {
            $this->data['captcha'] = '';
        }      

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/email.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/product/email.tpl';
        } else {
            $this->template = 'default/template/product/email.tpl';
        }
        
        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );
                
         $this->response->setOutput($this->render());        
      }
      public function sendEmail(){        
        if (isset($this->request->get['product_id'])) {
            $product_id = $this->request->get['product_id'];
        } else {
            $product_id = 0;
        }
        
        $this->load->model('catalog/product');
        $this->load->model('tool/image'); 
                        
        $product_info = $this->model_catalog_product->getProduct($product_id);
        
        if ($product_info) {            
            if (isset($this->session->data['qty'])) {
                $quantity = $this->session->data['qty'];
            } else {
                $quantity = 1;
            }
            if (isset($this->session->data['option'])) {
                $options = $this->session->data['option'];
            } else {
                $options = '';
            }
            if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], 100,100);
                } else {
                    $image = '';
                }            
            if (!isset($this->error['option'])) {
                $this->data['product_info'] = array(
                'product_name'      => $product_info['name'],
                'description'       => $product_info['description'],
                'model'             => $product_info['model'],
                'price'             => $product_info['price'],
                'image'             => $image,
                'qty'               => $quantity,
                'options'           => $options,
                'manufacturer'      => $product_info['manufacturer'],
                );                
            }
        } 
    }    
      private function validate() {
        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
              $this->error['name'] = $this->language->get('error_name');
        }

        if (!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
              $this->error['email'] = $this->language->get('error_email');
        }

        if ((utf8_strlen($this->request->post['enquiry']) < 10) || (utf8_strlen($this->request->post['enquiry']) > 3000)) {
              $this->error['enquiry'] = $this->language->get('error_enquiry');
        }

        if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
              $this->error['captcha'] = $this->language->get('error_captcha');
        }
        
        if (!$this->error) {
              return true;
        } else {
              return false;
        }        
      }

    public function captcha() {
        $this->load->library('captcha');
        
        $captcha = new Captcha();
        
        $this->session->data['captcha'] = $captcha->getCode();
        
        $captcha->showImage();
    }    
}
?>
