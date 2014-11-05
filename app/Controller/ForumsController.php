<?php

class ForumsController extends AppController {

    public $uses = array('User', 'UserInfo', 'UserBlockedList', '', 'AlbumPhoto', 'ReportPost', 'UserFriendList', 'ForumPost', 'LikePost', 'CommentPost');
    public $components = array('Paginator');

    function detail() {
//        $this->request->data["path_device"] = "file:///Users/admin/Library/Application Support/iPhone Simulator/7.1-64/Applications/0AF8AA63-BB39-4692-9F67-9AF466702245/HeartConnect.app/";
//        $this->request->data["user_find_id"] = 166;
//        $this->request->data["user_id"] = 166;
//        $this->request->data["forum_post_id"] = 42;
        $dataRequest = $this->request->data;
        $resultUser = array();
        $this->User->contain("UserInfo", "Album", "Album.AlbumPhoto");
        $user = $this->User->findById(@$dataRequest["user_find_id"]);
        $resultUser = $this->User->ConvertDatJson($user);
        $this->ForumPost->contain(array("LikePost", "CommentPost", "CommentPost.User", "CommentPost.User.UserInfo"));
        $val = $this->ForumPost->find("first", array(
            "joins" => array(
                array(
                    "table" => "user_infos",
                    "alias" => "UserInfo",
                    "type" => "LEFT",
                    "conditions" => array(
                        "UserInfo.user_id = ForumPost.user_id"
                    )
                )),
            "conditions" => array(
                "ForumPost.id" => $dataRequest["forum_post_id"]
            ),
            "order" => "ForumPost.modified DESC",
            "fields" => array("ForumPost.*", "UserInfo.*",)
                )
        );
        $result = array();
        switch ($val["ForumPost"]["type_forum"]) {
            case TYPE_IMAGE: {
                    $photos = $this->findImageFolder($val["ForumPost"]["id"]);
                    $val["ForumPost"]["photos"] = $photos;
                    break;
                }
            case TYPE_RECORDER: {
                    $file = $this->findRecorderFolder($val["ForumPost"]["id"]);
                    $val["ForumPost"]["recorder"] = $file;
                    break;
                }
            case TYPE_URL: {
                    $this->request->data["url"] = $val["ForumPost"]["detail"];
                    $request_url = $this->getURL(true);
                    $val["ForumPost"]["url"] = $request_url;
                    break;
                }
            default:
                break;
        }
        $val["ForumPost"]["name"] = $val["UserInfo"]["name"];
        $val["ForumPost"]["count_like"] = count($val["LikePost"]);
        $val["ForumPost"]["count_comment"] = count($val["CommentPost"]);
        $is_like = 0;
        foreach ($val["LikePost"] as $value) {
            if ($value["user_id"] == $dataRequest["user_id"]) {
                $is_like = 1;
                break;
            }
        }
        $val["ForumPost"]["timer"] = date("Y/m/d H:i:s", strtotime($val["ForumPost"]["modified"]));
        $val["ForumPost"]["modified"] = $this->displayPostTime($val["ForumPost"]["modified"]);
        $val["ForumPost"]["is_like"] = $is_like;
        $val["ForumPost"]["gender"] = $val["UserInfo"]["gender"];
        $val["ForumPost"]["avatar"] = Router::url('/', true) . $val["UserInfo"]["avatar"];
        if (!empty($val["ForumPost"]["content"])) {
            $val["ForumPost"]["content"] = $this->convertHtml($val["ForumPost"]["content"], @$dataRequest["path_device"]);
        }
        $result[] = array(
            "Forum" => $val["ForumPost"],
        );
        $comments = array();
        foreach ($val["CommentPost"] as $value) {
            if (!empty($value["User"]["UserInfo"]["avatar"])) {
                $value["User"]["UserInfo"]["avatar"] = Router::url('/', true) . $value["User"]["UserInfo"]["avatar"];
            }
            $comments[] = array(
                "name" => $value["User"]["UserInfo"]["name"],
                "avatar" => $value["User"]["UserInfo"]["avatar"],
                "comment" => $value["comment"],
                "user_id" => $value["user_id"],
                "id" => $value["id"],
                "gender" => (int) $value["User"]["UserInfo"]["gender"],
                "modified" => $this->displayPostTime($value["modified"]),
            );
        }
        $response = array(
            "User" => $resultUser,
            "Posts" => $result,
            "comment" => $comments
        );
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("data" => $response)));
    }

    function findLikeForum() {
        //$this->request->data["forum_post_id"] = 23;
        //$this->request->data["count"] = 6;
        $count = $this->request->data["count"];
        $dataRequest = $this->request->data;
        $data = $this->LikePost->find("all", array(
            "joins" => array(
                array(
                    "table" => "user_infos",
                    "alias" => "UserInfo",
                    "type" => "LEFT",
                    "conditions" => array(
                        "UserInfo.user_id = LikePost.user_id"
                    )
                )),
            "conditions" => array("LikePost.forum_post_id" => $dataRequest["forum_post_id"]),
            "fields" => array("UserInfo.*"),
        ));
        $result = array();
        foreach ($data as $key => $val) {
            if (!empty($val['UserInfo']['avatar'])) {
                $val['UserInfo']['avatar'] = Router::url('/', true) . $val['UserInfo']['avatar'];
            } else {
                $val['UserInfo']['avatar'] = "";
            }
            if ($key < $count) {
                $result[] = array(
                    "user_id" => $val["UserInfo"]["user_id"],
                    "name" => $val["UserInfo"]["name"],
                    "gender" => intval($val["UserInfo"]["gender"]),
                    "avatar" => $val["UserInfo"]["avatar"],
                );
            }
        }
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("data" => $result)));
    }

    function saveComment() {
        //  $this->request->data["user_id"] = 166;
        // $this->request->data["forum_post_id"] = 142;
        // $this->request->data["comment"] = "test thoi ma";
        $dataRequest = $this->request->data;
        $data = $this->ForumPost->findById($dataRequest["forum_post_id"]);
        $error = 1;
        if (!empty($data)) {
            $data["ForumPost"]["modified"] = @DboSource::expression('NOW()');
            $this->ForumPost->save($data);
            if ($this->CommentPost->save($dataRequest))
                $error = 0;
        }
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("errors" => $error)));
    }

    function deleteComment() {
        //  $this->request->data["id"] = 33;
        $dataRequest = $this->request->data;
        $data = $this->CommentPost->find("first", array(
            "conditions" => array(
                "CommentPost.id" => $dataRequest["comment_id"],
            )
        ));
        $error = 1;
        if (!empty($data)) {
            $error = 0;
            $this->CommentPost->delete($dataRequest["comment_id"]);
        }
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("data" => $error)));
    }

    function delete() {
        $dataRequest = $this->request->data;
        $data = $this->ForumPost->findById($dataRequest["forum_post_id"]);
        $error = 0;
        if (!empty($data)) {
            $this->ForumPost->delete($dataRequest["forum_post_id"]);
            $this->ReportPost->deleteAll(array('ReportPost.forum_post_id' => $dataRequest["forum_post_id"]), false);
            $this->LikePost->deleteAll(array('LikePost.forum_post_id' => $dataRequest["forum_post_id"]), false);
            $this->CommentPost->deleteAll(array('CommentPost.forum_post_id' => $dataRequest["forum_post_id"]), false);
            App::uses('Folder', 'Utility');
            $folder = new Folder();
            $targetdir = WWW_ROOT . 'forum' . DS . $dataRequest["forum_post_id"];
            $folder->delete($targetdir);
        }
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("errors" => $error)));
    }

    function report() {
//        $this->request->data["forum_post_id"] = "9";
//        $this->request->data["user_id"] = "166";
        $dataRequest = $this->request->data;
        $data = $this->ForumPost->findById($dataRequest["forum_post_id"]);
        $error = 1;
        if (!empty($data)) {
            $dataReport = $this->ReportPost->find("first", array(
                "conditions" => array(
                    "ReportPost.forum_post_id" => $this->request->data["forum_post_id"],
                    "ReportPost.user_id" => $this->request->data["user_id"],
                )
            ));
            $dataSave = array();
            if (!empty($dataReport)) {
                $dataReport["ReportPost"]["count"] = $dataReport["ReportPost"]["count"] + 1;
                $dataSave = $dataReport["ReportPost"];
            } else {
                $dataReport = array(
                    "forum_post_id" => $dataRequest["forum_post_id"],
                    "user_id" => $dataRequest["user_id"],
                    "count" => 1,
                );
            }
            $this->ReportPost->save($dataReport);
        }
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("errors" => $error)));
    }

    function uploadBackground() {
//        $this->request->data["user_id"] = 166;
//        $this->request->data["user_find_id"] = 166;
        $dataRequest = $this->request->data;
        $this->User->contain("UserInfo", "Album", "Album.AlbumPhoto");
        $user = $this->User->findById(@$dataRequest["user_find_id"]);
        App::uses('Folder', 'Utility');
        $namePath = 'img' . DS . $this->request->data["user_find_id"] . DS . "background";
        $realPath = WWW_ROOT . $namePath;
        $folder = new Folder();
        $folder->delete($realPath);
        $folder->create($realPath);
        $error = 1;
        $this->Img = $this->Components->load('Img');
        $ext = $this->Img->ext($_FILES['photo']['name']);
        $date = new DateTime();
        $path_destination_file = $date->getTimestamp() . "." . $ext;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $realPath . "/" . $path_destination_file)) {
            $error = 0;
            $user["UserInfo"]["background"] = $namePath . DS . $path_destination_file;
            $user["UserInfo"]["background"] = str_replace(DS, '/', $user["UserInfo"]["background"]);
            $this->UserInfo->save($user["UserInfo"]);
        }
        $resultUser = $this->User->ConvertDatJson($user);
        $response = array(
            "User" => $resultUser,
            "errors" => $error
        );
        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("data" => $response)));
    }

    function uploadAvatar() {
        $idUser = $this->request->data["user_id"];
        $this->autoLayout = false;
        $this->autoRender = false;
        $this->response->type("json");
        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');
        $this->User->contain("UserInfo", "Album", "Album.AlbumPhoto");
        $data = $this->User->find("first", array(
            "conditions" => array(
                "User.id" => $idUser,
            )
        ));

        if (empty($data['UserInfo'])) {
            return $this->response->body(json_encode(array("data" => 0, "errors" => __("can not find user"))));
        }
        $idAlbum = 0;
        $album_detail = array();
        if (empty($data["Album"])) {
            $saveAlbum = array(
                "user_id" => $idUser,
                "album_name" => ALBUM_NAME,
                "public_type" => PUBLIC_TYPE
            );
            $this->UserAlbum->create();
            $ret = $this->UserAlbum->save($saveAlbum, false);
            $idAlbum = $ret["UserAlbum"]["id"];
        } else {
            foreach ($data["Album"] as $value) {
                $idAlbum = $value["id"];
                if (!empty($value['AlbumPhoto'])) {
                    foreach ($value['AlbumPhoto'] as $keyPhoto => $valuePhoto) {
                        if ($valuePhoto['rank'] == 1) {
                            $album_detail = $valuePhoto;
                            break;
                        }
                    }
                }
            }
        }
        $count = 1;
        $pathUpload = "";
        $date = new DateTime();
        if (!empty($data)) {
            $id = $data['UserInfo']['user_id'];
            try {
                $new_image_name = $date->getTimestamp() . ".png";
                $namePath = 'img' . DS . $id . DS . "album";
                $realPath = WWW_ROOT . $namePath;
                $folder = new Folder();
                $folder->create($realPath);
                if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $realPath . "/" . $new_image_name)) {
                    return $this->response->body(json_encode(array("data" => 0)));
                }
                $namePath = 'img' . "/" . $id . "/" . "album";
                $pathUpload = $namePath . "/" . $new_image_name;
                $this->UserInfo->create();
                if ($count == 1) {
                    $data['UserInfo']['avatar'] = $pathUpload;
                    $this->UserInfo->save($data);
                }
                if (!empty($album_detail)) {
                    $file = new File(WWW_ROOT . $album_detail['photo']);
                    $file->delete();
                    $album_detail['photo'] = $pathUpload;
                } else {
                    $album_detail = array(
                        "album_id" => $idAlbum,
                        "photo" => $pathUpload,
                        "rank" => $count
                    );
                }
                $this->AlbumPhoto->save($album_detail);
                $resultUser = $this->User->ConvertDatJson($data);
                $response = array(
                    "User" => $resultUser,
                    "errors" => 0
                );
                return $this->response->body(json_encode(array("data" => $response)));
            } catch (Exception $e) {
                $response = array(
                    "User" => array(),
                    "errors" => 1
                );
                return $this->response->body(json_encode(array("data" => $response)));
            }
        } else {
            $resultUser = $this->User->ConvertDatJson($data);
            $response = array(
                "User" => $resultUser,
                "errors" => 0
            );
            return $this->response->body(json_encode(array("data" => $response)));
        }
        $response = array(
            "User" => array(),
            "errors" => 1
        );
        return $this->response->body(json_encode(array("data" => $response)));
    }

    function find() {
//        $this->request->data["path_device"] = "file:///Users/admin/Library/Application Support/iPhone Simulator/7.1-64/Applications/0AF8AA63-BB39-4692-9F67-9AF466702245/HeartConnect.app/";
//        $this->request->data["user_id"] = 166;
//        $this->request->data["my_post"] = 1;
        //       $this->request->data["user_find_id"] = 166;
//        $this->request->data["page"] = 1;
        $dataRequest = $this->request->data;
        $resultUser = array();
        $conditions = array();
        if (!empty($dataRequest["user_find_id"])) {
            if ($dataRequest["user_find_id"] == $dataRequest["user_id"]) {
                $this->User->contain("UserInfo", "Album", "Album.AlbumPhoto");
                $user = $this->User->findById(@$dataRequest["user_find_id"]);
                $resultUser = $this->User->ConvertDatJson($user);
            } else {
                $this->User->contain("UserInfo", "Album", "Album.AlbumPhoto");
                $user = $this->User->findById(@$dataRequest["user_find_id"]);
                if (!$user)
                    return $this->ApiNG();
                $resultUser = $this->User->ConvertDatJson($user);
                if (($this->UserFriendList->checkExistFriend($dataRequest["user_id"], $dataRequest["user_find_id"])))
                    $isFriends = ACCEPT;
                else if (($this->UserFriendList->checkExistRequest($dataRequest["user_id"], $dataRequest["user_find_id"])))
                    $isFriends = REQUEST;
                else
                    $isFriends = 0;
                $resultUser["isFriends"] = $isFriends;
            }
            $my_user = $this->UserInfo->findByUserId(@$dataRequest["user_id"]);
            if (!empty($my_user)) {
                if (!empty($my_user["UserInfo"]["avatar"])) {
                    $resultUser["my_avatar"] = Router::url('/', true) . $my_user["UserInfo"]["avatar"];
                }
                $resultUser["my_name"] = $my_user["UserInfo"]["name"];
                $resultUser["my_gender"] = $my_user["UserInfo"]["gender"];
            }
            $resultUser["user_api_cd"] = intval($resultUser["user_api_cd"]);
        }

        if (!empty($dataRequest["my_post"])) {
            $conditions["ForumPost.user_id"] = $dataRequest["user_find_id"];
            $order = "ForumPost.created DESC";
        } else {
            $blockList = $this->UserBlockedList->getBlockUser($dataRequest['user_id']);
            $friends = $this->UserFriendList->getListFriend($dataRequest['user_id']);
            $friends[] = -1;
            $friends[] = $dataRequest['user_id'];
            $blockList[] = -1;
            $blockList[] = -2;
            $conditions["ForumPost.user_id"] = array_unique($friends);
            $conditions["NOT"] = array("ForumPost.user_id" => array_unique($blockList));
            $order = "ForumPost.modified DESC";
        }
        $this->Paginator->settings = array(
            "joins" => array(
                array(
                    "table" => "user_infos",
                    "alias" => "UserInfo",
                    "type" => "LEFT",
                    "conditions" => array(
                        "UserInfo.user_id = ForumPost.user_id"
                    )
                )),
            "order" => $order,
            "fields" => array("ForumPost.*", "UserInfo.*",),
            "conditions" => $conditions,
            "page" => MAX(@$dataRequest["page"], 1),
            "recursive" => 1,
            "limit" => LIMIT_POST,
        );
        $this->ForumPost->contain(array("LikePost", "CommentPost"));
        $data = $this->Paginator->paginate('ForumPost');
        $result = array();
        foreach ($data as $val) {
            switch ($val["ForumPost"]["type_forum"]) {
                case TYPE_IMAGE: {
                        $photos = $this->findImageFolder($val["ForumPost"]["id"]);
                        $val["ForumPost"]["photos"] = $photos;
                        break;
                    }
                case TYPE_RECORDER: {
                        $file = $this->findRecorderFolder($val["ForumPost"]["id"]);
                        $val["ForumPost"]["recorder"] = $file;
                        break;
                    }
                case TYPE_URL: {
                        $this->request->data["url"] = $val["ForumPost"]["detail"];
                        $request_url = $this->getURL(true);
                        $val["ForumPost"]["url"] = $request_url;
                        break;
                    }
                default:
                    break;
            }
            $val["ForumPost"]["name"] = $val["UserInfo"]["name"];
            $val["ForumPost"]["count_like"] = count($val["LikePost"]);
            $val["ForumPost"]["count_comment"] = count($val["CommentPost"]);
            $is_like = 0;
            foreach ($val["LikePost"] as $value) {
                if ($value["user_id"] == $dataRequest["user_id"]) {
                    $is_like = 1;
                    break;
                    ;
                }
            }
            $val["ForumPost"]["timer"] = date("Y/m/d H:i:s", strtotime($val["ForumPost"]["created"]));
            $val["ForumPost"]["modified"] = $this->displayPostTime($val["ForumPost"]["created"]);
            $val["ForumPost"]["is_like"] = $is_like;
            $val["ForumPost"]["gender"] = $val["UserInfo"]["gender"];
            $val["ForumPost"]["avatar"] = Router::url('/', true) . $val["UserInfo"]["avatar"];
            $viewMore = 0;
            if (!empty($val["ForumPost"]["content"])) {
                $val["ForumPost"]["content"] = $this->convertHtml($val["ForumPost"]["content"], @$dataRequest["path_device"]);
                $height = $val["ForumPost"]["height_row"] % 100;
                $height = MIN($val["ForumPost"]["height_row"], LIMIT_HEIGHT_POST + $height);
                $text = "";
                if ($val["ForumPost"]["height_row"] > $height) {
                    $text = "...";
                    $viewMore = 1;
                }
                $val["ForumPost"]["height_row"] = $height + 5;
                $test = $height + 5;
                $val["ForumPost"]["content"] = "<div  style='height:{$test}px; overflow:hidden;'>" . $val["ForumPost"]["content"] . "{$text} <div>";
                $val["ForumPost"]["viewMore"] = $viewMore;
            }
            $result[] = array(
                "Forum" => $val["ForumPost"],
            );
        }
        $response = array(
            "User" => $resultUser,
            "Posts" => $result,
            "pageSum" => $this->request->params['paging']['ForumPost']['pageCount']
        );

        $this->autoRender = false;
        $this->response->type("json");
        $this->response->body(json_encode(array("data" => $response)));
    }

    public function addForumImages() {
        // $this->request->data["user_id"] = 166;
        // $this->request->data["content"] = "test asdvb asass gffa asss gggf ffg aas ssd as";
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        $user = $this->User->findById($dataRequest["user_id"]);
        if (!empty($user)) {
            $post = array(
                'user_id' => $user['User']['id'],
                'content' => @$dataRequest['content'],
                'height_row' => @$dataRequest['height_row'],
                'type_forum' => $dataRequest['type_forum']
            );
            if ($ret = $this->ForumPost->save($post)) {
                if (!empty($this->request->params['form'])) {
                    App::uses('Folder', 'Utility');
                    App::uses('File', 'Utility');
                    $this->Img = $this->Components->load('Img');
                    $targetdir = WWW_ROOT . 'forum' . DS . $ret["ForumPost"]["id"] . DS . "images";
                    $dir = new Folder($targetdir, true, 0777);
                    $i = 0;
                    foreach ($this->request->params['form'] as $photo) {
                        $ext = $this->Img->ext($photo['name']);
                        $name = "photo" . '-' . $i . '.' . $ext;
                        $upload = $this->Img->upload($photo['tmp_name'], $targetdir, $name);
                        if ($upload == 'Success') {
                            $path = WWW_ROOT . 'forum' . DS . $ret["ForumPost"]["id"] . DS . "images" . DS . 'small' . DS;
                            $dir2 = new Folder($path, true, 0777);
                            $this->Img->resampleGD($targetdir . DS . $name, $path, $name, 320, 600, false, 0);
                        }
                        $i++;
                    }
                }
                return $this->response->body(json_encode(array("data" => "")));
            }
        } else {
            return $this->output = array('error' => 1);
        }
    }

    function convertHtml($html, $path) {
        $html = preg_replace_callback('/<a(.*?)href(.*?)=(.*?)(\"|\')(.*?)(\"|\')(.*?)>/s', function($callback) {
            $result = '<a' . $callback['1'] . 'href' . $callback['2'] . '=' . $callback['3'] . '' . $callback['4'] . '' . $this->checkUrl($callback['5']) . '' . $callback['6'] . '' . $callback['7'] . '>';
            return $result;
        }, $html);
        $html = preg_replace_callback('/<img(.*?)src(.*?)=(.*?)(\"|\')(.*?)(\"|\')(.*?)>/s', function($callback) use($path) {
            $result = '<img' . $callback['1'] . 'src' . $callback['2'] . '=' . $callback['3'] . '' . $callback['4'] . '' . $path . pathinfo($callback['5'], PATHINFO_BASENAME) . '' . $callback['6'] . '' . $callback['7'] . '>';
            return $result;
        }, $html);
        return $html;
    }

    function saveLike() {
//        $this->request->data['user_id'] = "15";
//        $this->request->data['forum_post_id'] = "166";
//        $this->request->data['is_like'] = "0";
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        $user = $this->User->findById($dataRequest["user_id"]);
        if (!empty($user)) {
            $likePost = $this->LikePost->find("first", array(
                "conditions" => array(
                    "LikePost.user_id" => $dataRequest["user_id"],
                    "LikePost.forum_post_id" => $dataRequest["forum_post_id"],
                )
            ));
            if ($dataRequest['is_like'] == 1) {
                if (empty($likePost)) {
                    $post = array(
                        'user_id' => @$dataRequest['user_id'],
                        'forum_post_id' => @$dataRequest['forum_post_id'],
                    );
                    $this->LikePost->save($post);
                }
            } else {
                if (!empty($likePost)) {
                    $this->LikePost->delete($likePost["LikePost"]['id']);
                }
            }
            return $this->response->body(json_encode(array("error" => "0")));
        } else {
            return $this->response->body(json_encode(array("error" => "1")));
        }
    }

    public function addForumRecorder() {
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        $user = $this->User->findById($dataRequest["user_id"]);
        if (!empty($user)) {
            $post = array(
                'user_id' => $user['User']['id'],
                'content' => @$dataRequest['content'],
                'detail' => @$dataRequest['detail'],
                'height_row' => @$dataRequest['height_row'],
                'type_forum' => $dataRequest['type_forum']
            );
            if ($ret = $this->ForumPost->save($post)) {
                App::uses('Folder', 'Utility');
                $folder = new Folder();
                $targetdir = WWW_ROOT . 'forum' . DS . $ret["ForumPost"]["id"] . DS . "audio" . DS;
                $folder->create($targetdir);
                $date = new DateTime();
                $path_destination_file = $date->getTimestamp() . ".caf";
                if (move_uploaded_file($_FILES['audio']['tmp_name'], $targetdir . $path_destination_file)) {
                    $this->response->body(json_encode(array("error" => "1")));
                }
                return $this->response->body(json_encode(array("data" => "")));
            }
        } else {
            $this->response->body(json_encode(array("error" => "")));
        }
    }

    public function addForumUrl() {
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        $user = $this->User->findById($dataRequest["user_id"]);
        if (!empty($user)) {
            $post = array(
                'user_id' => $user['User']['id'],
                'content' => @$dataRequest['content'],
                'detail' => @$dataRequest['detail'],
                'height_row' => @$dataRequest['height_row'],
                'type_forum' => $dataRequest['type_forum']
            );
            if ($ret = $this->ForumPost->save($post)) {
                $this->response->body(json_encode(array("data" => "")));
            }
        } else {
            $this->response->body(json_encode(array("error" => "1")));
        }
    }

    public function addForum() {
//        $this->request->data["user_id"] = 166;
//        $this->request->data["content"] = "test asdvb asass <a href='ftp://Asd asdas dasdasdasd'> gffa asss gggf ffg aas ssd as";
//        $this->request->data["height_row"] = "165";
//        $this->request->data["type_forum"] = "5";
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        $user = $this->User->findById($dataRequest["user_id"]);
        if (!empty($user)) {
            $post = array(
                'user_id' => $user['User']['id'],
                'content' => @$dataRequest['content'],
                'height_row' => @$dataRequest['height_row'],
                'type_forum' => $dataRequest['type_forum']
            );
            if ($ret = $this->ForumPost->save($post)) {
                $this->response->body(json_encode(array("data" => "")));
            }
        } else {
            $this->response->body(json_encode(array("error" => "1")));
        }
    }

    public function addForumEmotions() {
        $this->autoRender = false;
        $this->response->type("json");
        $dataRequest = $this->request->data;
        $user = $this->User->findById($dataRequest["user_id"]);
        if (!empty($user)) {
            $post = array(
                'user_id' => $user['User']['id'],
                'content' => @$dataRequest['content'],
                'detail' => @$dataRequest['detail'],
                'height_row' => @$dataRequest['height_row'],
                'type_forum' => $dataRequest['type_forum']
            );
            if ($ret = $this->ForumPost->save($post)) {
                $this->response->body(json_encode(array("data" => "")));
            }
        } else {
            $this->response->body(json_encode(array("error" => "1")));
        }
    }

    function checkUrl($url) {
        $url = str_replace(array("\r\n", "\r", "\n"), '', trim($url));
        if (!preg_match('/\./', $url)) {
            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                $fail_url = str_replace(' ', '', $url);
                return 'http://' . $fail_url;
            } else {
                return $url;
            }
        } else {
            if (in_array(parse_url($url, PHP_URL_SCHEME), array('http', 'https'))) {
                if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                    $return = $url;
                } else {
                    $return = null;
                }
            } else {
                if (preg_match('/:\/\//', $url)) {
                    $return = null;
                } else {
                    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                        $url = "http://" . $url;
                    }
                    $return = $this->checkUrl($url);
                }
            }
        }
        return $return;
    }

    function getURL($return = false) {
        // $this->request->data["url"] = "http://leverages.jp";
        $url = strtolower(trim($this->request->data["url"]));
        $url = rtrim($url, "/");
        $this->autoRender = false;
        $this->response->type("json");
        $url = $this->checkUrl($url);
        $img_default = Router::url('/', true) . 'img/system/empty_link.png';
        $result = $result = array(
            'title' => "",
            'content' => '',
            'image' => $img_default,
            "date" => date("Y-m-d"),
            'url' => $url
        );
        if (empty($url)) {
            if (!empty($return)) {
                return $result;
            }
            return $this->response->body(json_encode(array("data" => array())));
        }
        $dataCache = Cache::read($url);
        if (!empty($dataCache)) {
            $date1 = date_create($dataCache["date"]);
            $date2 = date_create(date("Y-m-d"));
            if (!empty($date1) && !empty($date2)) {
                $dDiff = $date1->diff($date2);
                if ($dDiff->days <= CACHE_URL) {
                    if (!empty($return)) {
                        return $dataCache;
                    }
                    return $this->response->body(json_encode(array("data" => $dataCache)));
                }
            }
        }
        $handle = curl_init($url);
        $page = true;
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($handle, CURLOPT_ENCODING, "gzip");
        curl_setopt($handle, CURLOPT_URL, $url);
        //  curl_setopt($handle, CURLOPT_USERAGENT, $ua);
        curl_setopt($handle, CURLOPT_REFERER, $url);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            if (!empty($return)) {
                return $result;
            }
            return $this->response->body(json_encode(array("data" => array())));
        }
        curl_close($handle);
        if ($page) {
            $html = $data;
            $html = mb_convert_encoding($html, "UTF-8");
            libxml_use_internal_errors(true); // Yeah if you are so worried about using @ with warnings
            preg_match_all('/<img(?:.*)src=(\"|\')(http:\/\/.*?)(\"|\')/i', $html, $imgs);
            $img = $img_default;
            if (!empty($imgs['2']) && is_array($imgs['2'])) {
                foreach ($imgs['2'] as $key => $value) {
                    $url_img = pathinfo($value);
                    $img_size = getimagesize($value);
                    $img_width = 0;
                    $img_height = 0;
                    if ($img_size) {
                        $img_width = $img_size[0];
                        $img_height = $img_size[1];
                    }
                    if (!empty($url_img) && ( $url_img['extension'] == 'png' || $url_img['extension'] == 'jpg' || $url_img['extension'] == 'jpeg' ) && ( ($img_width >= 50) && ($img_height >= 50) )) {
                        $img = $value;
                        break;
                    }
                }
            }

            $doc = new DomDocument();
            $doc->loadHTML($html);
            $xpath = new DOMXPath($doc);
            $query = '//*/meta[starts-with(@property, \'og:\')]';
            $metas = $xpath->query($query);
            foreach ($metas as $meta) {
                $property = $meta->getAttribute('property');
                $content = $meta->getAttribute('content');
                $rmetas[$property] = $content;
            }
            $tags = get_meta_tags($url);
            preg_match("/<title>(.*)\<\/title>/s", $html, $tit);
            if (!empty($tit['1'])) {
                $title = $tit['1'];
            } elseif (!empty($tags['title'])) {
                $title = $tags['title'];
            } else {
                $title = pathinfo($url)['basename'];
            }

            if (!empty($rmetas)) {
                $image = (!empty($rmetas['og:image'])) ? $rmetas['og:image'] : $img;
                if (!empty($rmetas['og:description'])) {
                    $content = $rmetas['og:description'];
                } elseif (!empty($tags['description'])) {
                    $content = $tags['description'];
                } else {
                    $content = '';
                }
            } else {
                $image = $img;
                $content = (!empty($tags['description'])) ? $tags['description'] : '';
            }
            //   $title = mb_convert_encoding($title, "EUC-JP", "auto");
            $result = array(
                'title' => str_replace(array("\r\n", "\r", "\n"), "", trim($title)),
                'content' => str_replace(array("\r\n", "\r", "\n"), "", trim($content)),
                'image' => $image,
                "date" => date("Y-m-d"),
                'url' => $url
            );
        } else {
            $result = array(
                'title' => pathinfo($url)['basename'],
                'content' => '',
                'image' => $img_default,
                "date" => date("Y-m-d"),
                'url' => $url
            );
        }
        Cache::write($url, $result);
        if (!empty($return)) {
            return $result;
        }
        return $this->response->body(json_encode(array("data" => $result)));
    }

    public function findImageFolder($forum_id = null) {
        App::uses('Folder', 'Utility');
        $realPath = WWW_ROOT . 'forum' . DS . $forum_id . DS . "images" . DS . "small";
        $folder = new Folder($realPath);
        $files = $folder->find('.*', true);
        $result = array();
        asort($files);
        foreach ($files as $val) {
            $result[] = Router::url('/', true) . 'app/forum/' . $forum_id . '/images/' . 'small/' . $val;
        }
        return $result;
    }

    public function findRecorderFolder($forum_id = null) {
        App::uses('Folder', 'Utility');
        $realPath = WWW_ROOT . 'forum' . DS . $forum_id . DS . "audio";
        $folder = new Folder($realPath);
        $files = $folder->find('.*', true);
        asort($files);
        foreach ($files as $val) {
            return Router::url('/', true) . 'app/forum/' . $forum_id . '/audio/' . $val;
        }
        return "";
    }

}
