<?php
/**
 * Created by PhpStorm.
 * User: wuhongjia
 * Date: 03/11/2018
 * Time: 10:42
 */

class Response
{
    // 原始数据
    protected $data;

    // 当前的contentType content-type: text/html; charset=utf-8
    protected $contentType = 'application/json';

    // 字符集
    protected $charset = 'utf-8';

    //状态
    protected $code = 200;

    // 输出参数
    protected $options = [
        'json_encode_param' => JSON_UNESCAPED_UNICODE,
    ];
    // header参数
    protected $header = [];

    protected $content = null;
    protected $type = 'json';

    /**
     * 构造函数
     * @access   html
     * @param mixed $data    输出数据
     * @param int   $code
     * @param array $header
     * @param array $options 输出参数
     */
    public function __construct($data = '',$type = 'json', $code = 200, array $header = [], $options = [])
    {
        $this->data($data);
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->contentType($this->contentType, $this->charset);
        $this->header = array_merge($this->header, $header);
        $this->code   = $code;
        $this->type   = $type;
    }

    /**
     * 输出数据设置
     * @access html
     * @param mixed $data 输出数据
     * @return $this
     */
    public function data($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 页面输出类型
     * @param string $contentType 输出类型
     * @param string $charset     输出编码
     * @return $this
     */
    public function contentType($contentType, $charset = 'utf-8')
    {
        $this->header['Content-Type'] = $contentType . '; charset=' . $charset;
        return $this;
    }

    /**
     * 发送数据到客户端
     * @access html
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function send()
    {

        // 处理输出数据
        $data = $this->getContent();

        if (!headers_sent() && !empty($this->header)) {
            // 发送状态码
            http_response_code($this->code);
            // 发送头部信息
            foreach ($this->header as $name => $val) {
                if (is_null($val)) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        }

        echo $data;

        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }


    }

    /**
     * 处理数据
     * @access protected
     * @param mixed $data 要处理的数据
     * @return mixed
     */
    private function output_json($data)
    {
        try {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data, $this->options['json_encode_param']);

            if ($data === false) {
                throw new \InvalidArgumentException(json_last_error_msg());
            }

            return $data;
        } catch (\Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }
            throw $e;
        }
    }

    /**
     * 获取输出数据
     * @return mixed
     */
    public function getContent()
    {
        if (null == $this->content) {
            $content = null;
            if ($this->type === 'json'){
                $content = $this->output_json($this->data);

            }elseif ($this->type === 'xml'){
                $content = $this->data;
                $this->contentType('text/html', $this->charset);

            }

            if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable([
                    $content,
                    '__toString',
                ])
            ) {
                throw new \InvalidArgumentException(sprintf('variable type error： %s', gettype($content)));
            }

            $this->content = (string) $content;
        }
        return $this->content;
    }
}