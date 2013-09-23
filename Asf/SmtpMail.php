<?php
/**
 * Smtp mail sender
 use:
 $mail = new Asf_SmtpMail();
 $mail->setServerInfo('mail.***.com');

 $sign_info = "this is sign!";

 $mail->setFrom(array("yiyuanjian@gmail.com","Yuanjian Yi","yiyuanjian","password"));
 $mail->setCc(array(array("xxxx@xxx.com","xxx")));
 $mail->setTo(array(array("xxxx@xxx.com","xxx")));
 $mail->setBcc(array(array("xxx@xxx.com","xxx")));
 $mail->setMailInfo();

 $mail->setTitle("title");
 $mail->setContent("xcontent");
 $mail->addAttachment('filepath');
 $mail->setSign($sign_info);

 if (!$mail->send()) {
 var_dump($mail->getError());
 }
 *
 */
class Asf_SmtpMail {
    const CRLF = "\r\n";
    const NEWLINE = "\n";

    /**
     * mail server infomation
     *
     * @var array
     */
    private $serverinfo;

    /**
     * when connected a mail server,this value will be a resouce
     *
     * @var rescoure
     */
    private $connect_id;

    /**
     * error info, msg and seg shoud fill with user
     *
     * @var array
     */
    private $err_info = array('errno' => 0, 'seg' => '', 'errstr' => '', 'msg' => '');

    /**
     * About mail header，include 'To' 'From'...
     *
     * @var array
    */
    private $header = array();

    /**
     * mail content
     *
     * @var str
    */
    private $content;


    private $mail_info = array('charset' => 'utf-8');

    /**
     * attachment
     *
     * @var array
    */
    private $attachments = array();

    /**
     * Boundary
     *
     * @var array
    */
    private $boundary = "";

    public function __construct() {

    }

    public function __destruct() {

    }

    /**
     * connect to server
     *
     * @param int $timeout
     * @return bool
     */
    private function connect($timeout = 3) {

        if ($this->connect_id) {
            return true;
        }

        if (empty($this->serverinfo)) {
            $this->err_info['msg'] = "Please use setServerinfo before Send!";
            return false;
        }

        $fp = fsockopen($this->serverinfo['host'], $this->serverinfo['port'],
                $this->err_info['errno'], $this->err_info['errstr'], $timeout);
        if (empty($fp)) {
            //set error info
            $this->err_info['seg'] = "connect";
            $this->err_info['msg'] = 'Failed to connect server';
            return false;
        }

        $this->connect_id = $fp;

        if (!$this->checkResponseCode(220, 'Server response unsuccessful')) {
            return false;
        }

        return true;
    }

    /**
     * close connect
     *
     * @return bool
     */
    private function close() {
        if ($this->connect_id) {
            if (fclose($this->connect_id)) {
                $this->connect_id = 0;
                return true;
            }
        }

        return false;
    }

    /**
     * send some string to server
     *
     * @param string $cmd
     * @param string $argv
     * @return bool
     */
    private function sentRequest($cmd, $argv = '') {
        if (empty($cmd) && empty($argv)) {
            return fputs($this->connect_id, self::CRLF) ? true : false;
        }

        if (empty($argv)) {
            $command = $cmd;
        } elseif (empty($cmd)) {
            $command = $argv;
        } else {
            $command = $cmd . " " . $argv;
        }

        return fputs($this->connect_id, $command.self::CRLF) ? true : false;
    }

    /**
     * get the response of server
     *
     * @return str
     */
    private function getResponse() {
        $data = "";
        while ( $str = fgets($this->connect_id, 512) ) {
            $data .= $str;

            if (substr($str, 3, 1) == " ") {
                break;
            }
        }
        return $data;
    }

    /**
     * check code responsed
     *
     * @param int $valite_code
     * @param array $err_info
     * @return bool
     */
    private function checkResponseCode($valite_code, $err_msg = '') {
        $rs = $this->getResponse();
        $code = substr($rs, 0, 3);

        if ($code != $valite_code) {
            //set error info
            $this->err_info['errno'] = $code;
            $this->err_info['seg'] = "checkResponseCode";
            $this->err_info['errstr'] = $rs;
            $this->err_info['msg'] = $err_msg;
            return false;
        }

        return true;
    }

    /**
     * quit server
     *
     * @return bool
     */
    private function quit() {
        if (!$this->sentRequest('QUIT')) {
            return false;
        }

        if (!$this->checkResponseCode(221, 'Quit Error!')) {
            return false;
        }

        return true;
    }

    private function flush() {
        if (!$this->sentRequest('REST')) {
            return false;
        }

        if (!$this->checkResponseCode(250, 'REST Error!')) {
            return false;
        }

        return true;
    }

    /**
     * RFC FORMAT DATETIME
     */
    private function RFCDate() {
        $tz = date("Z");
        $tzs = ($tz < 0) ? "-" : "+";
        $tz = abs($tz);
        $tz = ($tz / 3600) * 100 + ($tz % 3600) / 60;
        $result = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $tzs, $tz);

        return $result;
    }

    private function encodeStr($str) {
        return chunk_split(base64_encode($str), 76, self::NEWLINE);
    }

    private function startBoundary($boundary, $content_type, $charset = '', $encoding = '') {
        if (empty($encoding)) {
            $encoding = $this->mail_info['encoding'];
        }
        if (empty($charset)) {
            $charset = $this->mail_info['charset'];
        }
        return "--" . $boundary . self::NEWLINE
        . "Content-Type: {$content_type}; charset = \"{$charset}\"" . self::NEWLINE .
        "Content-Transfer-Encoding: " . $encoding . self::NEWLINE;
    }

    private function endBoundary($boundary) {
        return "--" . $boundary . "--";
    }

    private function encodeMultiByteStr($str, $charset = 'utf-8') {
        return "=?" . $charset . "?B?" . base64_encode($str) . "?=";
    }

    /**
     * say hello after connect
     *
     * @return bool
     */
    private function sayHello() {
        if (!$this->sentRequest('EHLO',$this->serverinfo['host'])) {
            return false;
        }

        if (!$this->checkResponseCode(250, 'Failed say hello')) {
            return false;
        }

        return true;
    }

    /**
     * authorize use username and password
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    private function authorize($username, $password) {
        // Start authentication
        $this->sentRequest("AUTH LOGIN");

        if (!$this->checkResponseCode(334, 'Server Not Allow Auth')) {
            return false;
        }

        // Send encoded username
        $this->sentRequest(base64_encode($username));

        if (!$this->checkResponseCode(334, 'Username not accepted from server')) {
            return false;
        }

        // Send encoded password
        $this->sentRequest(base64_encode($password));

        if (!$this->checkResponseCode(235, 'Authentication failed from server')) {
            return false;
        }

        return true;
    }


    public function setServerInfo($host, $port = 25, $need_auth = true) {
        if (empty($host)) {
            return false; //TODO：check valited host
        }

        $this->serverinfo['host'] = $host;
        $this->serverinfo['port'] = $port;
        $this->serverinfo['auth'] = $need_auth;

        return true;
    }

    public function setMailInfo($isHtml = 'true', $charset = 'utf-8', $alt = '') {
        $this->mail_info['isHtml'] = $isHtml;
        $this->mail_info['charset'] = $charset;
        $this->mail_info['alt'] = $alt;
        $this->mail_info['content_type'] = "text/plain";

        //同时就设定content_type;
        if ($isHtml) {
            $this->mail_info['content_type'] = "text/html";
        }
        if ($alt) {
            $this->mail_info['content_type'] = "multipart/alternative";
        }
        $this->mail_info['encoding'] = "base64";

        return true;
    }

    /**
     * set mail title
     *
     * @param string $title
     * @param string $charset
     * @return string
     */
    public function setTitle($title) {
        $this->header['Subject'] = $title;
        return true;
    }

    /**
     * receiver
     *
     * @param array $receiver
     */
    public function setTo($to) {
        $this->header['To'] = $to;

        return true;
    }

    public function setFrom($from) {
        $this->header['From'] = $from;

        return true;
    }

    public function setCc($cc) {
        $this->header['Cc'] = $cc;

        return true;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function setBcc($bcc) {
        $this->header['Bcc'] = $bcc;

        return true;
    }

    public function addAttachment($path, $name = "", $encoding = "base64", $type = "application/octet-stream") {
        if (!is_file($path)) {
            $this->err_info['msg'] = $path." Not a file";
            return false;
        }

        $filename = basename($path);
        if ($name == "")
            $name = $filename;

        $cur = count($this->attachments);
        $this->attachments[$cur][0] = $path;
        $this->attachments[$cur][1] = $filename;
        $this->attachments[$cur][2] = $encoding;
        $this->attachments[$cur][3] = $type;
        $this->attachments[$cur][4] = false;

        return true;
    }

    public function setSign($sign) {
        $this->content .= $sign;

        return true;
    }



    public function send() {
        //check
        if (count($this->header['From']) == 0 || count($this->header['To']) == 0) {
            $this->err_info['msg'] = "You must give sender and receiver";

            return false;
        }

        $header = $this->makeHeader();

        $body = $this->makeBody();

        if (!$this->connect()) {
            return false;
        }

        if (!$this->sayHello()) {
            return false;
        }

        if (!$this->authorize($this->header['From'][2], $this->header['From'][3])) {
            return false;
        }

        //sender info
        $this->sentRequest("MAIL FROM: <" . $this->header['From'][0] . ">");
        if (!$this->checkResponseCode(250, 'RCPT TO FAILED!')) {
            return false;
        }
        //mail receiver
        foreach ( $this->header['To'] as $to ) {
            $this->sentRequest('RCPT TO: <' . $to[0] . '>');
            if (!$this->checkResponseCode(250, '') && !$this->checkResponseCode(251, 'also not 251')) {
                return false;
            }
        }

        if (count($this->header['Cc']) > 0) {
            foreach ( $this->header['Cc'] as $to ) {
                $this->sentRequest('RCPT TO: <' . $to[0] . '>');
                if (!$this->checkResponseCode(250, '') && !$this->checkResponseCode(251, 'also not 251')) {
                    return false;
                }
            }
        }

        if (count($this->header['Bcc']) > 0) {
            foreach ( $this->header['Bcc'] as $to ) {
                $this->sentRequest('RCPT TO: <' . $to[0] . '>');
                if (!$this->checkResponseCode(250, '') && !$this->checkResponseCode(251, 'also not 251')) {
                    return false;
                }
            }
        }

        //send data
        if (!$this->sentRequest('DATA')) {
            return false;
        }
        if (!$this->checkResponseCode(354, 'DATA command not accepted from server')) {
            return false;
        }

        $content = explode("\n",str_replace("\r\n","\n",$header . $body));
        foreach ($content as $v) {
            $this->sentRequest('', $v);
        }

        $this->sentRequest('', self::CRLF . "." . self::CRLF);

        if (!$this->checkResponseCode(250, "DATA not accepted from server")) {
            return false;
        }

        if (!$this->quit()) {
            return false;
        }
        $this->close();

        return true;
    }



    private function makeHeader() {
        $result = array();

        // Set the boundaries
        $uniq_id = md5(uniqid(time()));
        $this->boundary[1] = "b1_" . $uniq_id;
        $this->boundary[2] = "b2_" . $uniq_id;

        $result[] = "Date: " . $this->RFCDate();

        $result[] = "Return-Path: " . $this->header['From'][0];

        foreach ( $this->header['To'] as $to ) {
            $tmp[] = $this->encodeMultiByteStr($to[1]) . " <" . $to[0] . ">";
        }
        $result[] = "To: " . implode(",\n\t", $tmp);
        unset($tmp);

        if (count($this->header['Cc']) > 0) {
            foreach ( $this->header['Cc'] as $to ) {
                $tmp[] = $this->encodeMultiByteStr($to[1]) . " <" . $to[0] . ">";
            }
            $result[] = "Cc: " . implode(",\n\t", $tmp);
            unset($tmp);
        }

        if (count($this->header['Bcc']) > 0) {
            foreach ( $this->header['Bcc'] as $to ) {
                $tmp[] = $this->encodeMultiByteStr($to[1]) . " <" . $to[0] . ">";
            }
            $result[] = "Bcc: " . implode(",\n\t", $tmp);
            unset($tmp);
        }

        $result[] = "From: \"" . $this->encodeMultiByteStr($this->header['From'][1])
        . "\" <" . $this->header['From'][0] .">";



        $result[] = "Subject: " . $this->encodeMultiByteStr($this->header['Subject']);

        $pos = strpos($this->header['From'][0], '@');
        $domain = substr($this->header['From'][0], $pos + 1);
        $result[] = "Message-ID: <" . $uniq_id . "@" . $domain . ">";

        if (empty($this->header['Priority'])) {
            $this->header['Priority'] = 3;
        }
        $result[] = "X-Priority: " . $this->header['Priority'];
        $result[] = "X-Mailer: " . "Yuanjian Mailer [version 0.1]";
        $result[] = "MIME-Version: 1.0";

        //mail content-type
        if (count($this->attachments) < 1 && strlen($this->mail_info['alt']) < 1) {
            $msg_type = "plain";
        } else {
            if (count($this->attachments) > 0)
                $msg_type = "attachments";
            if (strlen($this->mail_info['alt']) > 0 && count($this->attachments) < 1)
                $msg_type = "alt";
            if (strlen($this->mail_info['alt']) > 0 && count($this->attachments) > 0)
                $msg_type = "alt_attachments";
        }

        $this->mail_info['msg_type'] = $msg_type;

        switch ($msg_type) {
            case "plain" :
                $result[] = "Content-Transfer-Encoding: " . $this->mail_info['encoding'];
                $result[] = "Content-Type: text/plain; charset=\"{$this->mail_info['charset']}\"";
                break;
            case "attachments" :
            case "alt_attachments":
                $result[] = "Content-Type: multipart/mixed;";
                $result[] = "\tboundary=\"" . $this->boundary[1] . "\"";
                break;
            case "alt" :
                $result[] = "Content-Type: multipart/alternative;";
                $result[] = "\tboundary=\"" . $this->boundary[1] . "\"";
                break;
        }

        $result[] = self::NEWLINE . self::NEWLINE;

        return implode(self::NEWLINE, $result);
    }

    /**
     * make mail body
     *
     */
    private function makeBody() {
        switch ($this->mail_info['msg_type']) {
            case "alt" :
                $result[] = $this->startBoundary($this->boundary[1], "text/plain");
                $result[] = $this->encodeStr($this->mail_info['alt']);
                $result[] = $this->startBoundary($this->boundary[1], "text/html");
                $result[] = $this->encodeStr($this->content);
                $result[] = $this->endBoundary($this->boundary[1]);
                break;
            case "plain" :
                $result[] = $this->encodeStr($this->content);
                break;
            case "attachments" :
                $result[] = $this->startBoundary($this->boundary[1], $this->mail_info['content_type']);
                $result[] = $this->encodeStr($this->content);
                $result[] = $this->AttachAll();
                break;
            case "alt_attachments" :
                $result[] = "--" . $this->boundary[1];
                $result[] = "Content-Type: multipart/alternative;".self::NEWLINE
                . "\tboundary=\"{$this->boundary[2]}\"" .self::NEWLINE;
                $result[] = $this->startBoundary($this->boundary[2], "text/plain");
                $result[] = $this->encodeStr($this->mail_info['alt']);
                $result[] = $this->startBoundary($this->boundary[2], "text/html");
                $result[] = $this->encodeStr($this->content);
                $result[] = $this->endBoundary($this->boundary[2]);
                $result[] = $this->AttachAll();
                break;
        }

        return implode(self::NEWLINE, $result);
    }

    /**
     * encode attachment
     *
     * @return unknown
     */
    private function AttachAll() {
        $attachment_count = count($this->attachments);
        for($i = 0; $i < $attachment_count; $i++) {
            $path = $this->attachments[$i][0];
            $filename = $this->attachments[$i][1];
            $encoding = $this->attachments[$i][2];
            $type = $this->attachments[$i][3];
            $disposition = $this->attachments[$i][4];

            $mime[] = "--" . $this->boundary[1];
            $mime[] = "Content-Type: {$type}; name=\"{$filename}\"";
            $mime[] = "Content-Transfer-Encoding: " . $encoding;
            $mime[] = "Content-Disposition: {$disposition}; filename=\"{$filename}\"" . self::NEWLINE;
            //encode
            $mime[] = chunk_split(base64_encode(file_get_contents($path)), 76, self::NEWLINE);

        }
        $mime[] = "--" . $this->boundary[1] . "--";

        return implode(self::NEWLINE, $mime);
    }

    /**
     * get error info
     *
     * @return array
     */
    public function getError() {
        return $this->err_info;
    }

    /**
     * output debug info
     *
     * @param mixed $info
     * @return bool
     */
    private function out_debug($info) {
        if (is_array($info)) {
            foreach ($info as $k => $v) {
                $tmp[] = $k."=>".$v;
            }
            $info = explode("\n", $tmp);
        }

        echo $info;

        return true;
    }
}