<?php
// vim: foldmethod=marker
/**
 *	Ethna_ActionForm.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	�ե������������Ѵ��ե饰: Ⱦ�ѥ��ʢ����ѥ���
 */
define('CONVERT_1BYTE_KANA', 1 << 0);

/**
 *	�ե������������Ѵ��ե饰: ���ѿ�����Ⱦ�ѿ���
 */
define('CONVERT_2BYTE_NUMERIC', 1 << 1);

/**
 *	�ե������������Ѵ��ե饰: ���ѥ���ե��٥åȢ�Ⱦ�ѥ���ե��٥å�
 */
define('CONVERT_2BYTE_ALPHABET', 1 << 2);

/**
 *	�ե������������Ѵ��ե饰: ��������
 */
define('CONVERT_LTRIM',	1 << 3);

/**
 *	�ե������������Ѵ��ե饰: ��������
 */
define('CONVERT_RTRIM',	1 << 4);

/**
 *	�ե������������Ѵ��ե饰: ����������
 */
define('CONVERT_LRTRIM', (1 << 3) | (1 << 4));

/**
 *	�ե������������Ѵ��ե饰: ���ѱѿ���Ⱦ�ѱѿ�/����������
 */
define('CONVERT_2BYTE', (1 << 1) | (1 << 2) | (1 << 3) | (1 << 4));


// {{{ Ethna_ActionForm
/**
 *	���������ե����९�饹
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_ActionForm
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	array	�ե����������
	 */
	var $form = array();

	/**
	 *	@var	array	�ե�������
	 */
	var $form_vars = array();

	/**
	 *	@var	array	���ץꥱ�������������
	 */
	var $app_vars = array();

	/**
	 *	@var	array	���ץꥱ�������������(��ư���������פʤ�)
	 */
	var $app_ne_vars = array();

	/**
	 *	@var	object	Ethna_ActionError	action error���֥�������
	 */
	var $action_error;

	/**
	 *	@var	object	Ethna_ActionError	action error���֥�������(��ά��)
	 */
	var $ae;

	/**
	 *	@var	object	Ethna_I18N	i18n���֥�������
	 */
	var $i18n;

	/**
	 *	@var	array	�ե������������
	 */
	var $def = array('name', 'required', 'max', 'min', 'regexp', 'custom', 'convert', 'form_type', 'type');

	/**#@-*/

	/**
	 *	Ethna_ActionForm���饹�Υ��󥹥ȥ饯��
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	controller���֥�������
	 */
	function Ethna_ActionForm(&$controller)
	{
		$this->i18n =& $controller->getI18N();
		$this->action_error =& $controller->getActionError();
		$this->ae =& $this->action_error;

		if (isset($_SERVER['REQUEST_METHOD']) == false) {
			return;
		}

		if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
			$http_vars =& $_POST;
		} else {
			$http_vars =& $_GET;
		}

		// �ե�����������
		foreach ($this->form as $name => $value) {
			// ��ά������
			foreach ($this->def as $k) {
				if (isset($value[$k]) == false) {
					$this->form[$name][$k] = null;
				}
			}

			$type = to_array($value['type']);
			if ($type[0] == VAR_TYPE_FILE) {
				@$this->form_vars[$name] =& $_FILES[$name];
			} else {
				if (isset($http_vars[$name]) == false) {
					if (isset($http_vars["{$name}_x"])) {
						@$this->form_vars[$name] = $http_vars["{$name}_x"];
					} else {
						@$this->form_vars[$name] = null;
					}
				} else {
					@$this->form_vars[$name] = $http_vars[$name];
				}
			}
		}
	}

	/**
	 *	�ե������ͤΥ�������(R)
	 *
	 *	@access	public
	 *	@param	string	$name	�ե������ͤ�̾��
	 *	@return	mixed	�ե�������
	 */
	function get($name)
	{
		if (isset($this->form_vars[$name])) {
			return $this->form_vars[$name];
		}
		return null;
	}

	/**
	 *	�ե�������������������
	 *
	 *	@access	public
	 *	@return	array	�ե����������
	 */
	function getDef()
	{
		return $this->form;
	}

	/**
	 *	�ե��������ɽ��̾���������
	 *
	 *	@access	public
	 *	@param	string	$name	�ե������ͤ�̾��
	 *	@return	mixed	�ե������ͤ�ɽ��̾
	 */
	function getName($name)
	{
		if (isset($this->form[$name]) == false) {
			return null;
		}
		if (isset($this->form[$name]['name']) && $this->form[$name]['name'] != null) {
			return $this->form[$name]['name'];
		}

		// try message catalog
		return $this->i18n->get($name);
	}

	/**
	 *	�ե������ͤؤΥ�������(W)
	 *
	 *	@access	public
	 *	@param	string	$name	�ե������ͤ�̾��
	 *	@param	string	$value	���ꤹ����
	 */
	function set($name, $value)
	{
		$this->form_vars[$name] = $value;
	}

	/**
	 *	�ե������ͤ�����ˤ����֤�
	 *
	 *	@access	public
	 *	@param	bool	$escape	HTML���������ץե饰(true:���������פ���)
	 *	@return	array	�ե������ͤ��Ǽ��������
	 */
	function &getArray($escape = true)
	{
		$retval = array();

		$this->_getArray($this->form_vars, $retval, $escape);

		return $retval;
	}

	/**
	 *	���ץꥱ������������ͤΥ�������(R)
	 *
	 *	@access	public
	 *	@param	string	$name	����
	 *	@return	mixed	���ץꥱ�������������
	 */
	function getApp($name)
	{
		if (isset($this->app_vars[$name]) == false) {
			return null;
		}
		return $this->app_vars[$name];
	}

	/**
	 *	���ץꥱ������������ͤΥ�������(W)
	 *
	 *	@access	public
	 *	@param	string	$name	����
	 *	@param	mixed	$value	��
	 */
	function setApp($name, $value)
	{
		$this->app_vars[$name] = $value;
	}

	/**
	 *	���ץꥱ������������ͤ�����ˤ����֤�
	 *
	 *	@access	public
	 *	@param	boolean	$escape	HTML���������ץե饰(true:���������פ���)
	 *	@return	array	�ե������ͤ��Ǽ��������
	 */
	function getAppArray($escape = true)
	{
		$retval = array();

		$this->_getArray($this->app_vars, $retval, $escape);

		return $retval;
	}

	/**
	 *	���ץꥱ�������������(��ư���������פʤ�)�Υ�������(R)
	 *
	 *	@access	public
	 *	@param	string	$name	����
	 *	@return	mixed	���ץꥱ�������������
	 */
	function getAppNE($name)
	{
		if (isset($this->app_ne_vars[$name]) == false) {
			return null;
		}
		return $this->app_ne_vars[$name];
	}

	/**
	 *	���ץꥱ�������������(��ư���������פʤ�)�Υ�������(W)
	 *
	 *	@access	public
	 *	@param	string	$name	����
	 *	@param	mixed	$value	��
	 */
	function setAppNE($name, $value)
	{
		$this->app_ne_vars[$name] = $value;
	}

	/**
	 *	���ץꥱ�������������(��ư���������פʤ�)������ˤ����֤�
	 *
	 *	@access	public
	 *	@param	boolean	$escape	HTML���������ץե饰(true:���������פ���)
	 *	@return	array	�ե������ͤ��Ǽ��������
	 */
	function getAppNEArray($escape = false)
	{
		$retval = array();

		$this->_getArray($this->app_ne_vars, $retval, $escape);

		return $retval;
	}

	/**
	 *	�ե����������ˤ����֤�(��������)
	 *
	 *	@access	private
	 *	@param	array	&$vars		�ե�����(��)������
	 *	@param	array	&$retval	����ؤ��Ѵ����
	 *	@param	bool	$escape		HTML���������ץե饰(true:���������פ���)
	 */
	function _getArray(&$vars, &$retval, $escape)
	{
		foreach (array_keys($vars) as $name) {
			if (is_array($vars[$name])) {
				$retval[$name] = array();
				$this->_getArray($vars[$name], $retval[$name], $escape);
			} else {
				$retval[$name] = $escape ? htmlspecialchars($vars[$name]) : $vars[$name];
			}
		}
	}

	/**
	 *	�ե������͸��ڥ᥽�å�
	 *
	 *	@access	public
	 *	@return	int		ȯ���������顼�ο�
	 */
	function validate()
	{
		foreach ($this->form as $name => $value) {
			$type = to_array($value['type']);
			if ($type[0] == VAR_TYPE_FILE) {
				// �ե����븡��
				$tmp_name = to_array($this->form_vars[$name]['tmp_name']);
				$valid_keys = array();
				foreach ($tmp_name as $k => $v) {
					if (is_uploaded_file($tmp_name[$k]) == false) {
						// �ե�����ʳ����ͤ�̵��
						continue;
					}
					$valid_keys[] = $k;
				}
				if (count($valid_keys) == 0 && $value['required']) {
					$this->_handleError($name, $value, E_FORM_REQUIRED);
					continue;
				} else if (count($valid_keys) == 0 && $value['required'] == false) {
					continue;
				}

				if (is_array($this->form_vars[$name]['tmp_name'])) {
					if (is_array($value['type']) == false) {
						// ñ�����Υե�����������Ϥ���Ƥ���
						$this->_handleError($name, $value, E_FORM_WRONGTYPE_SCALAR);
						continue;
					}

					// �ե�����ǡ�����ƹ���
					$files = array();
					foreach ($valid_keys as $k) {
						$files[$k]['name'] = $this->form_vars[$name]['name'][$k];
						$files[$k]['type'] = $this->form_vars[$name]['type'][$k];
						$files[$k]['tmp_name'] = $this->form_vars[$name]['tmp_name'][$k];
						$files[$k]['size'] = $this->form_vars[$name]['size'][$k];
					}
					$this->form_vars[$name] = $files;

					// ����γ����Ǥ��Ф��븡��
					foreach (array_keys($this->form_vars[$name]) as $key) {
						$this->_validate($name, $this->form_vars[$name][$key], $value);
					}
				} else {
					if (is_array($value['type'])) {
						// �������Υե�����˥����顼�ͤ��Ϥ���Ƥ���
						$this->_handleError($name, $value, E_FORM_WRONGTYPE_ARRAY);
						continue;
					}
					if (count($valid_keys) == 0) {
						$this->form_vars[$name] = null;
					}
					$this->_validate($name, $this->form_vars[$name], $value);
				}
			} else {
				if (is_array($this->form_vars[$name])) {
					if (is_array($value['type']) == false) {
						// �����顼������Υե�����������Ϥ���Ƥ���
						$this->_handleError($name, $value, E_FORM_WRONGTYPE_SCALAR);
						continue;
					}

					// ����γ����Ǥ��Ф����Ѵ�/����
					foreach (array_keys($this->form_vars[$name]) as $key) {
						$this->form_vars[$name][$key] = $this->_convert($this->form_vars[$name][$key], $value['convert']);
						$this->_validate($name, $this->form_vars[$name][$key], $value);
					}
				} else {
					if ($this->form_vars[$name] == null && is_array($value['type']) && $value['required'] == false) {
						// ���󷿤Ǿ�ά�ĤΤ�Τ��ͼ��Τ���������Ƥʤ��Ƥ⥨�顼�Ȥ��ʤ�
						continue;
					} else if (is_array($value['type'])) {
						$this->_handleError($name, $value, E_FORM_WRONGTYPE_ARRAY);
						continue;
					}
					$this->form_vars[$name] = $this->_convert($this->form_vars[$name], $value['convert']);
					$this->_validate($name, $this->form_vars[$name], $value);
				}
			}
		}

		if ($this->ae->count() == 0) {
			// �ɲø��ڥ᥽�å�
			$this->_validatePlus();
		}

		return $this->ae->count();
	}

	/**
	 *	�����å��᥽�å�(���쥯�饹)
	 *
	 *	@access	public
	 *	@param	string	$name	�ե��������̾
	 *	@return	array	�����å��оݤΥե�������(���顼��̵������null)
	 */
	function check($name)
	{
		if (is_null($this->form_vars[$name]) || $this->form_vars[$name] === "") {
			return null;
		}

		// Ethna_Backend������
		$c =& getController();
		$this->backend =& $c->getBackend();

		return to_array($this->form_vars[$name]);
	}

	/**
	 *	�����å��᥽�å�: �����¸ʸ��
	 *
	 *	@access	public
	 *	@param	string	$name	�ե��������̾
	 *	@return	object	Ethna_Error	���顼���֥�������(���顼��̵������null)
	 */
	function &checkVendorChar($name)
	{
		$string = $this->form_vars[$name];
		for ($i = 0; $i < strlen($string); $i++) {
			/* JIS13��Τߥ����å� */
			$c = ord($string{$i});
			if ($c < 0x80) {
				/* ASCII */
			} else if ($c == 0x8e) {
				/* Ⱦ�ѥ��� */
				$i++;
			} else if ($c == 0x8f) {
				/* JIS X 0212 */
				$i += 2;
			} else if ($c == 0xad || ($c >= 0xf9 && $c <= 0xfc)) {
				/* IBM��ĥʸ�� / NEC����IBM��ĥʸ�� */
				return $this->ad->add(E_FORM_INVALIDCHAR, $name, '{form}�˵����¸ʸ�������Ϥ���Ƥ��ޤ�');
			} else {
				$i++;
			}
		}

		return null;
	}

	/**
	 *	�����å��᥽�å�: bool��
	 *
	 *	@access	public
	 *	@param	string	$name	�ե��������̾
	 *	@return	object	Ethna_Error	���顼���֥�������(���顼��̵������null)
	 */
	function &checkBoolean($name)
	{
		$form_vars = $this->check($name);
		if ($form_vars == null) {
			return null;
		}
		foreach ($form_vars as $v) {
			if ($v != "0" && $v != "1") {
				return $this->ae->add($name, '{form}�����������Ϥ��Ƥ�������', E_FORM_INVALIDCHAR);
			}
		}
		return null;
	}

	/**
	 *	�����å��᥽�å�: �᡼�륢�ɥ쥹
	 *
	 *	@access	public
	 *	@param	string	$name	�ե��������̾
	 *	@return	object	Ethna_Error	���顼���֥�������(���顼��̵������null)
	 */
	function &checkMailaddress($name)
	{
		$form_vars = $this->check($name);
		if ($form_vars == null) {
			return null;
		}
		foreach ($form_vars as $v) {
			if (Ethna_Util::checkMailaddress($v) == false) {
				return $this->ae->add($name, '{form}�����������Ϥ��Ƥ�������', E_FORM_INVALIDCHAR);
			}
		}
		return null;
	}

	/**
	 *	�����å��᥽�å�: URL
	 *
	 *	@access	public
	 *	@param	string	$name	�ե��������̾
	 *	@return	object	Ethna_Error	���顼���֥�������(���顼��̵������null)
	 */
	function &checkURL($name)
	{
		$form_vars = $this->check($name);
		if ($form_vars == null) {
			return null;
		}
		foreach ($form_vars as $v) {
			if (preg_match('/^(http:\/\/|https:\/\/|ftp:\/\/)/', $v) == 0) {
				return $this->ae->add($name, '{form}�����������Ϥ��Ƥ�������', E_FORM_INVALIDCHAR);
			}
		}
		return null;
	}

	/**
	 *	�ե������ͤ�hidden�����Ȥ����֤�
	 *
	 *	@access	public
	 *	@param	array	$include_list	���󤬻��ꤵ�줿��硢��������˴ޤޤ��ե�������ܤΤߤ��оݤȤʤ�
	 *	@param	array	$exclude_list	���󤬻��ꤵ�줿��硢��������˴ޤޤ�ʤ��ե�������ܤΤߤ��оݤȤʤ�
	 *	@return	string	hidden�����Ȥ��Ƶ��Ҥ��줿HTML
	 */
	function getHiddenVars($include_list = null, $exclude_list = null)
	{
		$hidden_vars = "";
		foreach ($this->form as $key => $value) {
			if (is_array($include_list) == true && in_array($key, $include_list) == false) {
				continue;
			}
			if (is_array($exclude_list) == true && in_array($key, $exclude_list) == true) {
				continue;
			}

			$form_value = $this->form_vars[$key];
			if (is_array($form_value) == false) {
				$form_value = array($form_value);
				$form_array = false;
			} else {
				$form_array = true;
			}
			foreach ($form_value as $k => $v) {
				if ($form_array) {
					$form_name = "$key" . "[$k]";
				} else {
					$form_name = $key;
				}
				$hidden_vars .= sprintf("<input type=\"hidden\" name=\"%s\" value=\"%s\" />\n",
					$form_name, htmlspecialchars($v));
			}
		}
		return $hidden_vars;
	}

	/**
	 *	�ե������͸��ڤΥ��顼������Ԥ�
	 *
	 *	@access	protected
	 *	@param	string		$name	�ե��������̾
	 *	@param	array		$value	�ե��������
	 *	@param	int			$code	���顼������
	 */
	function _handleError($name, $value, $code)
	{
		if ($code == E_FORM_REQUIRED) {
			switch ($value['form_type']) {
			case FORM_TYPE_TEXT:
			case FORM_TYPE_PASSWORD:
			case FORM_TYPE_TEXTAREA:
			case FORM_TYPE_SUBMIT:
				$message = "{form}�����Ϥ��Ʋ�����";
				break;
			case FORM_TYPE_SELECT:
			case FORM_TYPE_RADIO:
			case FORM_TYPE_CHECKBOX:
			case FORM_TYPE_FILE:
				$message = "{form}�����򤷤Ʋ�����";
				break;
			}
		} else if ($code == E_FORM_WRONGTYPE_SCALAR) {
			$message = "{form}�ˤϥ����顼�ͤ����Ϥ��Ʋ�����";
		} else if ($code == E_FORM_WRONGTYPE_ARRAY) {
			$message = "{form}�ˤ���������Ϥ��Ʋ�����";
		} else if ($code == E_FORM_WRONGTYPE_INT) {
			$message = "{form}�ˤϿ���(����)�����Ϥ��Ʋ�����";
		} else if ($code == E_FORM_WRONGTYPE_FLOAT) {
			$message = "{form}�ˤϿ���(����)�����Ϥ��Ʋ�����";
		} else if ($code == E_FORM_WRONGTYPE_DATETIME) {
			$message = "{form}�ˤ����դ����Ϥ��Ʋ�����";
		} else if ($code == E_FORM_WRONGTYPE_BOOLEAN) {
			$message = "{form}�ˤ�1�ޤ���0�Τ����ϤǤ��ޤ�";
		} else if ($code == E_FORM_MIN_INT) {
			$this->ae->add($name, "{form}�ˤ�%d�ʾ�ο���(����)�����Ϥ��Ʋ�����", $code, $def['min']);
			return;
		} else if ($code == E_FORM_MIN_FLOAT) {
			$this->ae->add($name, "{form}�ˤ�%f�ʾ�ο���(����)�����Ϥ��Ʋ�����", $code, $def['min']);
			return;
		} else if ($code == E_FORM_MIN_DATETIME) {
			$this->ae->add($name, "{form}�ˤ�%s�ʹߤ����դ����Ϥ��Ʋ�����", $code, $def['min']);
			return;
		} else if ($code == E_FORM_MIN_FILE) {
			$this->ae->add($name, "{form}�ˤ�%dKB�ʾ�Υե��������ꤷ�Ʋ�����", $code, $def['min']);
			return;
		} else if ($code == E_FORM_MIN_STRING) {
			$this->ae->add($name, "{form}�ˤ�%dʸ���ʾ����Ϥ��Ʋ�����", $code, $def['min']);
			return;
		} else if ($code == E_FORM_MAX_INT) {
			$this->ae->add($name, "{form}�ˤ�%d�ʲ��ο���(����)�����Ϥ��Ʋ�����", $code, $def['max']);
			return;
		} else if ($code == E_FORM_MAX_FLOAT) {
			$this->ae->add($name, "{form}�ˤ�%f�ʲ��ο���(����)�����Ϥ��Ʋ�����", $code, $def['max']);
			return;
		} else if ($code == E_FORM_MAX_DATETIME) {
			$this->ae->add($name, "{form}�ˤ�%s���������դ����Ϥ��Ʋ�����", $code, $def['max']);
			return;
		} else if ($code == E_FORM_MAX_FILE) {
			$this->ae->add($name, "{form}�ˤ�%dKB�ʲ��Υե��������ꤷ�Ʋ�����", $code, $def['max']);
			return;
		} else if ($code == E_FORM_MAX_STRING) {
			$this->ae->add($name, "{form}��%dʸ���ʲ������Ϥ��Ʋ�����", $code, $def['max']);
			return;
		} else if ($code == E_FORM_REGEXP) {
			$message = "{form}�����������Ϥ��Ƥ�������";
		}

		$this->ae->add($name, $message, $code);
	}

	/**
	 *	�桼��������ڥ᥽�å�(�ե������ʹ֤�Ϣ�ȥ����å���)
	 *
	 *	@access	protected
	 */
	function _validatePlus()
	{
	}

	/**
	 *	�ե������͸��ڥ᥽�å�(����)
	 *
	 *	@access	private
	 *	@param	string	$name		�ե��������̾
	 *	@param	mixed	$var		�ե�������
	 *	@param	array	$def		�ե����������
	 *	@param	bool	$test		���顼���֥���������Ͽ�ե饰(true:��Ͽ���ʤ�)
	 *	@return	bool	true:���ｪλ false:���顼
	 */
	function _validate($name, $var, $def, $test = false)
	{
		$type = is_array($def['type']) ? $def['type'][0] : $def['type'];

		// required
		if ($type == VAR_TYPE_FILE) {
			if ($def['required'] && ($var == null || $var['size'] == 0)) {
				if ($test == false) {
					$this->_handleError($name, $def, E_FORM_REQUIRED);
				}
				return false;
			}
		} else {
			if ($def['required'] && strlen($var) == 0) {
				if ($test == false) {
					$this->_handleError($name, $def, E_FORM_REQUIRED);
				}
				return false;
			}
		}

		// type
		if (is_array($var) == false && @strlen($var) > 0) {
			if ($type == VAR_TYPE_INT) {
				if (!preg_match('/^-?\d+$/', $var)) {
					if ($test == false) {
						$this->_handleError($name, $def, E_FORM_WRONGTYPE_INT);
					}
					return false;
				}
			} else if ($type == VAR_TYPE_FLOAT) {
				if (!preg_match('/^-?\d+$/', $var) && !preg_match('/^-?\d+\.\d+$/', $var)) {
					if ($test == false) {
						$this->_handleError($name, $def, E_FORM_WRONGTYPE_FLOAT);
					}
					return false;
				}
			} else if ($type == VAR_TYPE_DATETIME) {
				if (strtotime($var) == -1) {
					if ($test == false) {
						$this->_handleError($name, $def, E_FORM_WRONGTYPE_DATETIME);
					}
					return false;
				}
			} else if ($type == VAR_TYPE_BOOLEAN) {
				if ($var != "1" && $var != "0") {
					if ($test == false) {
						$this->_handleError($name, $def, E_FORM_WRONGTYPE_BOOLEAN);
					}
					return false;
				}
			} else if ($type == VAR_TYPE_STRING) {
				// nothing to do
			}
		}

		// min
		if ($type == VAR_TYPE_INT && $var !== "") {
			if (!is_null($def['min']) && $var < $def['min']) {
				if ($test == false) {
					$this->_handleError($name, $def, E_FORM_MIN_INT);
				}
				return false;
			}
		} else if ($type == VAR_TYPE_FLOAT && $var !== "") {
			if (!is_null($def['min']) && $var < $def['min']) {
				if ($test == false) {
					$this->_handleError($name, $def, E_FORM_MIN_FLOAT);
				}
				return false;
			}
		} else if ($type == VAR_TYPE_DATETIME && $var !== "") {
			if (!is_null($def['min'])) {
				$t_min = strtotime($def['min']);
				$t_var = strtotime($var);
				if ($t_var < $t_min) {
					if ($test == false) {
						$this->_handleError($name, $def, E_FORM_MIN_DATETIME);
					}
				}
				return false;
			}
		} else if ($type == VAR_TYPE_FILE) {
			if (!is_null($def['min'])) {
				$st = @stat($var['tmp_name']);
				if ($st[7] < $def['min'] * 1024) {
					if ($test == false) {
						$this->_handleError($name, $def, E_FORM_MIN_FILE);
					}
					return false;
				}
			}
		} else {
			if (!is_null($def['min']) && strlen($var) < $def['min'] && $var !== "") {
				if ($test == false) {
					$this->_handleError($name, $def, E_FORM_MIN_STRING);
				}
				return false;
			}
		}

		// max
		if ($type == VAR_TYPE_INT && $var !== "") {
			if (!is_null($def['max']) && $var > $def['max']) {
				if ($test == false) {
					$this->_handleError($name, $def, E_FORM_MAX_INT);
				}
				return false;
			}
		} else if ($type == VAR_TYPE_FLOAT && $var !== "") {
			if (!is_null($def['max']) && $var > $def['max']) {
				if ($test == false) {
					$this->_handleError($name, $def, E_FORM_MAX_FLOAT);
				}
				return false;
			}
		} else if ($type == VAR_TYPE_DATETIME && $var !== "") {
			if (!is_null($def['max'])) {
				$t_min = strtotime($def['max']);
				$t_var = strtotime($var);
				if ($t_var > $t_min) {
					if ($test == false) {
						$this->_handleError($name, $def, E_FORM_MAX_DATETIME);
					}
				}
				return false;
			}
		} else if ($type == VAR_TYPE_FILE) {
			if (!is_null($def['max'])) {
				$st = @stat($var['tmp_name']);
				if ($st[7] > $def['max'] * 1024) {
					if ($test == false) {
						$this->_handleError($name, $def, E_FORM_MAX_FILE);
					}
					return false;
				}
			}
		} else {
			if (!is_null($def['max']) && strlen($var) > $def['max'] && $var !== "") {
				if ($test == false) {
					$this->_handleError($name, $def, E_FORM_MAX_STRING);
				}
				return false;
			}
		}

		// regexp
		if ($def['regexp'] != null && strlen($var) > 0 && preg_match($def['regexp'], $var) == 0) {
			if ($test == false) {
				$this->_handleError($name, $def, E_FORM_REGEXP);
			}
			return false;
		}

		// custom (TODO: respect $test flag)
		if ($def['custom'] != null) {
			$this->{$def['custom']}($name);
		}

		return true;
	}

	/**
	 *	�ե饰�˽����ե������ͤ��Ѵ�����
	 *
	 *	@access	private
	 *	@param	mixed	$value	�ե�������
	 *	@param	int		$flag	�Ѵ��ե饰
	 *	@return	mixed	�Ѵ����
	 */
	function _convert($value, $flag)
	{
		$flag = intval($flag);

		$key = "";
		if ($flag & CONVERT_LTRIM) {
			$value = ltrim($value);
		}
		if ($flag & CONVERT_RTRIM) {
			$value = rtrim($value);
		}
		if ($flag & CONVERT_1BYTE_KANA) {
			$key .= "K";
		}
		if ($flag & CONVERT_2BYTE_NUMERIC) {
			$key .= "n";
		}
		if ($flag & CONVERT_2BYTE_ALPHABET) {
			$key .= "r";
		}
		if ($key == "") {
			return $value;
		}

		return mb_convert_kana($value, $key);
	}
}
// }}}
?>