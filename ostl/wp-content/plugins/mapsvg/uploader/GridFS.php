<?php
/*
 * jQuery File Upload Plugin PHP Class 5.9
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
namespace Uploader;
class GridFS
{
    protected $options;

    function __construct($options=null) {

        $this->mongo  = new \Mongo;
        $this->gridfs = $this->mongo->files->getGridFS();
        $this->db     = \Core\Core::getMongo();



        $this->options = array(
            'script_url' => '/uploader/',
            'upload_dir' => $_SERVER[DOCUMENT_ROOT].'/source/Uploader/files/',
            'upload_url' => '/images/',
            'param_name' => 'files',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'delete_type' => 'DELETE',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'accept_file_types' => '/.+$/i',
            'max_number_of_files' => null,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to true to rotate images based on EXIF meta data, if available:
            'orient_image' => false,
            'image_versions' => array(),
            // Записывать сначала во временную папку
            'tmp' => false
        );
        if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }


    }

    protected function getFullUrl() {
      	return (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
    		(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		(isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		'/images/';

    }

    protected function set_file_delete_url($file) {
        $file->delete_url  = $this->options['script_url'].'?file='.$file->id;
        $file->delete_type = $this->options['delete_type'];
        if ($file->delete_type !== 'DELETE')
            $file->delete_url .= '&_method=DELETE';
    }

    /**
     * Получить отдельный объект файла
     */
    public function get_file_object($file_id) {

        $fs = $this->gridfs->findOne(array('_id'=> new \MongoID($file_id)));
        // TODO удалять все версии файла - тумбнейлы и тд

        if ($fs->file['_id']) {
            $file       = (object)$fs->file;
            $file->id   = (string)$file->_id;
            $file->name = $file->filename;
            $file->size = $file->length;
            $file->url  = $this->getFullUrl().$file->id;

            //$versions_cursor = $this->gridfs->find(array('parent'=>$file->id));

            //if($versions_cursor)
            //    foreach($versions_cursor as $c)
            //        $file->versions->thumbnail = $this->getFullUrl().(string)$c->file['_id'];

            $this->set_file_delete_url($file);
            return $file;
        }
        return null;
    }

    /**
     * Получить все файлы из данной папки
     */
    public function get_file_objects($path = false, $limit = 0) {
        if(!$path) $path =  $this->options['upload_url'];
        $fc = $this->gridfs->find( array('path'=>$path) )->limit($limit);
        if($fc)
            foreach($fc as $c)
                $files[] = $this->get_file_object($c->file['_id']);
        return $files;
    }


    /**
     * Изменить размер загруженной картинки перед сохранением в базу
     */
    public function scale_uploaded_image($uploaded_file, $options){

        $src = new \Imagick();

        $src->readImageBlob( file_get_contents($uploaded_file) );

        if($options['crop']){
            $src->cropThumbnailImage($options['max_width'], $options['max_height']);
        }else{
            $src->thumbnailImage($options['max_width'], $options['max_height']);
        }

        if($options['max_width'] < 601){
            $src->unsharpMaskImage(0.6, 1, 0.5, 0.05);
        }

        // Put the data of the resized image into a variable
        $src->setImageFormat('jpeg');
        $src->setImageCompressionQuality(90);

        $bytes = $src->getimageblob();

        $src->destroy();

        return $bytes;
   }



    /**
     * Изменить размер картинки из базы
     */
    public function create_scaled_image($file_id, $version, $options){

        $new_file_path = $options['upload_url'];

        //$this->deleteFile($new_file_path);

        $_file_id      = new \MongoID($file_id);

        $imagick       = new \Imagick();
        $image         = $this->gridfs->findOne(array('_id'=>$_file_id));

        $file = (array)$image->file;
        $file = array('filename' => $image->file['filename'],
                      'type'     => 'image/jpeg',
                      'name'     => $image->file['filename'],
                      'path'     => $new_file_path,
                      'parent'   => $_file_id,
                      'version'  => $version
                      );



        $imagick->readImageBlob( $image->getBytes() );

        if($options['crop']){
            $imagick->cropThumbnailImage($options['max_width'], $options['max_height']);
        }else{
            $imagick->thumbnailImage($options['max_width'], $options['max_height']);
        }

        if($width < 601){
            $imagick->unsharpMaskImage(0.6, 1, 0.5, 0.05);
        }

        $imagick->setImageFormat('jpeg');
        $imagick->setImageCompressionQuality(90);

        $data = $imagick->getimageblob();


        $_id = $this->gridfs->storeBytes( $imagick->getImageBlob(), $file );

        $imagick->destroy();

        $this->gridfs->update(array('_id'=>$_file_id), array( '$set'=>array('versions.'.$version=>$_id)) );

        return $_id;
   }

    /**
     * Проверка на ошибки в загружемом файле
     */
    protected function has_error($uploaded_file, $file, $error) {
        if ($error) {
            return $error;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            return 'acceptFileTypes';
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            return 'maxFileSize';
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            return 'minFileSize';
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
            ) {
            return 'maxNumberOfFiles';
        }
        return $error;
    }

    protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    /**
     * Подрезать имя файла
     */
    protected function trim_file_name($name, $type) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Add missing file extension for known image types:
        if (strpos($file_name, '.') === false &&
            preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $file_name .= '.'.$matches[1];
        }
        if ($this->options['discard_aborted_uploads']) {
            while(is_file($this->options['upload_dir'].$file_name)) {
                $file_name = $this->upcount_name($file_name);
            }
        }
        return $file_name;
    }

    /**
     * Загрузка файла
     */
    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error) {



        $file           = new \stdClass();
        $file->filename = $this->trim_file_name($name, $type);
        $file->name     = $name;
        $file->path     = $this->options['upload_url'];
        $file->type     = $type;

        $error          = $this->has_error($uploaded_file, $file, $error);

        if($this->options['overwrite'] && $error == 'maxNumberOfFiles') $error = false;


        if (!$error && $file->filename){

            if (filesize($uploaded_file) === intval($size)) {
            // РАЗМЕР ФАЙЛА ДО И ПОСЛЕ ОТПРАВКИ ДОЛЖЕН БЫТЬ ОДИНАКОВЫЙ - ЕСЛИ НЕТ, ЗНАЧИТ ДОСТАВКА ОБОРВАЛАСЬ

                // СОХРАНЯЕМ ФАЙЛ В GRIDFS:

                if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // Если файл загружен...

                    if( $this->options['overwrite'] ) {
                        $old_file = $this->gridfs->findOne(array('path'=>$file->path));
                        if($old_file->file['_id'])
                            $this->gridfs->remove(array('$or'=>array( array('_id'=>$old_file->file['_id']), array('parent'=>$old_file->file['_id']))));
                    }

                    if($this->options['max_width'] || $this->options['max_height']){
                    // Меняем размер, если нужно
                        $_id = $this->gridfs->storeBytes( $this->scale_uploaded_image($uploaded_file, $this->options), (array)$file );
                        $g   = $this->gridfs->findOne(array('_id'=>$_id));
                    }else{
                    // Либо сохраняем как есть
                        $_id = $this->gridfs->storeFile($uploaded_file, (array)$file);
                        $g   = $this->gridfs->findOne(array('_id'=>$_id));
                    }
                }

                $file->_id  = $_id;
                $file->id   = (string)$_id;
                $file->url  = $this->options['upload_url'].$file->id;
                $file->size = $g->file['length'];
                $file->name = $g->file['filename'];

                // ГЕНЕРИМ ТУМБНЕЙЛЫ, ЕСЛИ НУЖНО
                if($this->options['image_versions'])
                  foreach($this->options['image_versions'] as $version => $options) {
                    if ($thumb_id = $this->create_scaled_image((string)$_id, $version, $options)) {
                          $file->versions->{$version} = $thumb_id;
                    }
                  }
            } elseif ($this->options['discard_aborted_uploads']){
                $file->error = 'abort';
            }

            $this->set_file_delete_url($file);

        } else {
            $file->error = $error;
        }


        // Прикрепление картинки к товару
        if($this->options['filetype']=='item_images' && !$this->options['tmp']){
            $p = explode('/', $this->options['upload_url']);
            $item_id = $p[1];
            $this->setItemImage((array)$file, $item_id);
        }

        return $file;
    }

    /**
     * Получить файлы или один файл
     */
    public function get() {
        $file_id = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null;
        if ($file_id) {
            $info = $this->get_file_object($file_id);
        } else {
            $info = $this->get_file_objects();
        }
        header('Content-type: application/json');
        echo json_encode($info);
    }

    /**
     * Запрос на действие через $_POST
     */
    public function post($echo = true) {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->deleteFile();
        }
        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : null;
        $info = array();

        if ($upload && is_array($upload['tmp_name'])) {
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    isset($_SERVER['HTTP_X_FILE_NAME']) ?
                        $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                        $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                        $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                    $upload['error'][$index]
                );
            }
        } elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
            $info[] = $this->handle_file_upload(
                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                isset($_SERVER['HTTP_X_FILE_NAME']) ?
                    $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ?
                        $upload['name'] : null),
                isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                    $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ?
                        $upload['size'] : null),
                isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                    $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ?
                        $upload['type'] : null),
                isset($upload['error']) ? $upload['error'] : null
            );
        }
        header('Vary: Accept');
        $json = json_encode($info);
        $redirect = isset($_REQUEST['redirect']) ?
            stripslashes($_REQUEST['redirect']) : null;
        if ($redirect) {
            header('Location: '.sprintf($redirect, rawurlencode($json)));
            return;
        }
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        if($echo)
            echo $json;
    }

   /**
    * Удаление файла из GridFS
    */
   public function deleteFile($id = null, $delete_connections = true, $format = 'json'){

        $file_id = $id ? $id : (isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null);

        $c = $this->gridfs->findOne(array('_id'=> new \MongoID($file_id)));

        // TODO удалять все версии файла - тумбнейлы и тд
        if($c->file['_id']){

            if($delete_connections){
                // Удаляем привязку картинки внутри товара
                $e = explode('/', $c->file['path']);
                if($e[2]=='item_images'){
                    echo '1 ';
                    $item_id = new \MongoID($e[1]);
                    echo (string)$item_id;
                    $this->db->items->update( array('_id'=>$item_id), array('$pull'=>array('images'=>array('id'=>$c->file['_id']))) );
                }
            }

            $this->gridfs->remove( array('$or'=>array(array('_id' => $c->file['_id']), array('parent'=>$c->file['_id']) )) );
            $success = 1;
        }

        if($format == 'json'){
            header('Content-type: application/json');
            echo json_encode($success);
        }else{
            return $success;
        }
   }

   /**
    * Переместить фото товара из временной папки в постоянную
    */
    public function setItemImages($buy_id, $item_id){

        $images = $this->gridfs->find(array('path'=>  new \MongoRegex( "/^tmp\/buy\/".(string)$buy_id."\/item_images\/$/" )));

        foreach($images as $i)
            $this->setItemImage($i->file, $item_id);
    }

   /**
    * Присвоить картинку товару
    */
    public function setItemImage($file, $item_id){

        $item_images = array('id'=>$file['_id'], 'versions'=>$file['versions']);

        $this->gridfs->update(array('_id'=>$file['_id']), array('$set'=>array('path'=>'item/'.$item_id.'/item_images/')));

        foreach ($file['versions'] as $version=>$file_id){
            $path =  'item/'.$item_id.'/item_images/'.$version;
            $this->gridfs->update(array('_id'=>$file_id), array('$set'=>array('path'=>$path)));
        }



        $this->db->items->update( array('_id'=>new \MongoID($item_id)), array('$push'=>array('images'=>$item_images)) );
    }


}