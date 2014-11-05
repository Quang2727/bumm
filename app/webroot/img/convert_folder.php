<?php
for ($i=1; $i <=255 ; $i++) {
    $album = $i.'/album';
    $avatar = $i.'/avatar';
    $malbum = '00/'.$i.'/album';
    if(is_dir($album)) {
        if(is_dir($malbum)) {
            $files = scandir($album);
            unset($files['0']);
            unset($files['1']);
            if(($key = array_search('Thumbs.db', $files)) !== false) {
                unset($files[$key]);
            }
            foreach ($files as $key => $value) {
                copy($album.'/'.$value, $malbum.'/'.$value);
            }
            var_dump($files);
        } else {
            mkdir('00/'.$i);
            mkdir('00/'.$i.'/album');
            $files = scandir($album);
            unset($files['0']);
            unset($files['1']);
            if(($key = array_search('Thumbs.db', $files)) !== false) {
                unset($files[$key]);
            }
            foreach ($files as $key => $value) {
                copy($album.'/'.$value, $malbum.'/'.$value);
            }
        }
    }

    if(is_dir($avatar)) {
        if(is_dir($malbum)) {
            $files = scandir($avatar);
            unset($files['0']);
            unset($files['1']);
            if(($key = array_search('Thumbs.db', $files)) !== false) {
                unset($files[$key]);
            }
            foreach ($files as $key => $value) {
                copy($avatar.'/'.$value, $malbum.'/'.$value);
            }
            var_dump($files);
        } else {
            mkdir('00/'.$i);
            mkdir('00/'.$i.'/album');
            $files = scandir($avatar);
            unset($files['0']);
            unset($files['1']);
            if(($key = array_search('Thumbs.db', $files)) !== false) {
                unset($files[$key]);
            }
            foreach ($files as $key => $value) {
                copy($avatar.'/'.$value, $malbum.'/'.$value);
            }
        }
    }



}
?>