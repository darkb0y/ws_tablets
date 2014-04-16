<?php

class UsersController {
    public function GETAction($path, $data) {
        // imagine retreving data from models
        $users = $this->getUsers();
        $friend = $this->getFriends();

        if(isset($path[2])) {
            if(isset($path[3]) && $path[3] == 'friends') {
                return $friends[(int)$path[2]];
            } else {
                return $users[(int)$path[2]];
            }
        } else {
            return $users;
        }
    }

    public function POSTAction($path, $data) {
        // sanitise and validate data please!

        // create a user from params
        $new_user['name'] = isset($data['name']) ? $data['name'] : '';
        $new_user['house'] = isset($data['house']) ? $data['house'] : '';
        $new_user['age'] = isset($data['age']) ? $data['age'] : '';

        // save the user, return the new id
        $new_user['self'] = 'http://' . $_SERVER['HTTP_HOST'] . '/rest/users/5';

        // redirect user
        header('HTTP/1.1 201 Created');
        header('Location: http://api.local/rest/users/5');        
        return $new_user;
    }

    protected function getUsers() {
        $users = array();
        // imagine retreving data from models

        $users[0] = array('name' => 'Harry Potter',
            'age' => 14,
            'house' => 'Gryffindor',
            'self' => urlencode($_SERVER['HTTP_HOST'] . '/rest/users/0')
        );
        $users[1] = array('name' => 'Hermione Granger',
            'age' => 14,
            'house' => 'Gryffindor',
            'self' => $_SERVER['HTTP_HOST'] . '/rest/users/1'
        );
        $users[2] = array('name' => 'Ron Weasley',
            'age' => 14,
            'house' => 'Gryffindor',
            'self' => $_SERVER['HTTP_HOST'] . '/rest/users/2'
        );
        $users[3] = array('name' => 'Ginny Weasley',
            'age' => 13,
            'house' => 'Gryffindor',
            'self' => $_SERVER['HTTP_HOST'] . '/rest/users/3'
        );
        $users[4] = array('name' => 'Cho Chang',
            'age' => 15,
            'house' => 'Ravenclaw',
            'self' => $_SERVER['HTTP_HOST'] . '/rest/users/4'
        );
    }

    protected function getFriends() {
        $friends[0] = array( // Harry's friends
            'http://api.local/rest/user/1',
            'http://api.local/rest/user/2',
            'http://api.local/rest/user/3',
            'http://api.local/rest/user/4'
        );

        $friends[1] = array( // Hermione's friends 
            'http://api.local/rest/user/0',
            'http://api.local/rest/user/2',
            'http://api.local/rest/user/3'
        );

    }
}
?>