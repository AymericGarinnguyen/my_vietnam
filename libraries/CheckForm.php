<?php

namespace Libraries;

class CheckForm
{

    /**
     * check XSS injection and replace script tag
     * 
     * @param string $post
     * @return string
     */
    public static function checkSecurity($post): string
     {
        $security = htmlspecialchars($post);
        $security = str_replace(['&lt;script', '&lt;script&gt;', '&lt;/script&gt;',], '', $security);

        return $security;
    }

    /**
     * check if input is empty
     * 
     * @param string $name
     * @param string $post
     * @return void
     */
    public static function emptyInput(string $name, string $post, string $traduction): void
    {
        if (empty($post)) {
            throw new \DomainException(serialize([
                'name' => $name,
                'message' => $traduction." : Ce champ ne peut pas être vide"
            ]));
        }
    }

    /**
     * check if value match with regex
     * 
     * @param string $data
     * @param string $caractAutor
     * @param int $lgMin
     * @param int $lgMax
     * @param string $name
     * @return void
     */
    public static function fieldCheck(string $data, string $caractAutor, int $min, int $max, string $name, string $traduction): void
    {
        if (!preg_match('/^' . $caractAutor . '{' . $min . ',' . $max . '}+$/', $data)) {
            throw new \DomainException(serialize([
                'name' => $name,
                'message' => $traduction . " : Ce champ n'est pas valide"
            ]));
        }
    }

    /**
     * check if phone value match with regex
     * 
     * @param string $data
     * @param string $name
     * @param string $traduction
     * @return void
     */
    public static function phoneCheck(string $data, string $name, string $traduction): void
    {
        if (!preg_match('/^((\+\d+(\s|-)?)|0)\d(\s|-|\.)?(\d{2}(\s|-|\.)?){4}$/', $data)) {
            throw new \DomainException(serialize([
                'name' => $name,
                'message' => $traduction . " : Ce champ n'est pas valide"
            ]));
        }
    }

    /**
     * check if credit card value match with regex
     * 
     * @param string $data
     * @param string $name
     * @param string $traduction
     * @return void
     */
    public static function visaCheck(string $data, string $name, string $traduction): void
    {
        if (!preg_match('/^(\d{4}(\s)?){4}$/', $data)) {
            throw new \DomainException(serialize([
                'name' => $name,
                'message' => $traduction . " : Ce champ n'est pas valide"
            ]));
        }
    }

    /**
     * check if password's value match with regex
     * 
     * @param string $data
     * @param string $caractAutor
     * @param string $name
     * @return void
     */
    public static function passwordCheck(string $data, string $caractAutor, string $name, string $traduction): void
    {
        if (!preg_match('/' . $caractAutor . '/', $data)) {
            throw new \DomainException(serialize([
                'name' => $name,
                'message' => $traduction . " : Ce champ n'est pas valide"
            ]));
        }
    }

    /**
     * Check email
     * 
     * @param string $email
     * @return void
     */
    public static function emailCheck(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \DomainException(serialize([
                'name' => 'email',
                'message' => "Email : Ce champ n'est pas valide"
            ]));
        }
    }

    /**
     * Check if value lenght is correct
     * 
     * @param string $data
     * @param int $min
     * @param int $max
     * @param string $name
     * @return void
     */
    public static function lengthCheck(string $data, int $min, int $max, string $name, string $traduction): void
    {
        if (strlen($data) < $min || strlen($data) > $max) {
            throw new \DomainException(serialize([
                'name' => $name,
                'message' => $traduction . " : Le nombre de caratères du champ doit être compris entre $min et $max."
            ]));
        }
    }

    /**
     * Check if value lenght is correct
     * 
     * @param int $month
     * @param int $year
     * @return void
     */
    public static function dateCheck(int $month, int $year): void
    {
        if ($year === intval(date("Y"))) {
            if ($month < intval(date("m"))) {
                throw new \DomainException(serialize([
                    'name' => 'group-date',
                    'message' => "La date sélectectionée est antérieure à la date actuelle."
                ]));
            }
        }
    }

    /**
     * Check $_FILE, move temp picture in image folder and return the file name
     * 
     * @param array $data
     * @return string
     */
    public static function pictureCheck(array $data): string
    {
        // if size is smaller than 1mo
        if ($data['size'] < (1024 * 1024 * 1)) {
            $type = explode('/', $data['type']);
            // if type is image
            if ($type[0] === 'image') {
                // save the extension file in variable
                if ($type['1'] === 'jpeg') {
                    $extension = "jpg";
                } elseif ($type['1'] === 'svg+xml') {
                    $extension = "svg";
                } else {
                    $extension = $type['1'];
                }

                // create a new name for the picture
                $filename = preg_replace("/\s+/", "", strtolower($_POST['name'])) . '.' . $extension;
                //upload the picture
                move_uploaded_file($_FILES['picture']['tmp_name'], 'assets/img/shop/' . $filename);
                return $filename;
            } else {
                throw new \DomainException(serialize([
                    'name' => 'image',
                    'message' => "Le fichier n'est pas une image"
                ]));
            }
        } else {
            throw new \DomainException(serialize([
                'name' => 'image',
                'message' => "Le poids de l'image est trop lourd"
            ]));
        }
    }
}
