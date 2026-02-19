<?php
class nqvPasswordResetService {

    public function requestReset(string $email): bool {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if(!$email) throw new InvalidArgumentException('Email inválido');

        $user = new nqvUsers(['email' => $email]);

        if(!$user->exists()) return false;

        return $user->sendResetPasswordConfirmMail();
    }

    public function resetPassword(string $token, string $password, string $rePassword): bool {
        if(empty($token)) throw new InvalidArgumentException('Token inválido');

        $user = new nqvUsers(['token' => $token]);

        if(!$user->exists()) throw new RuntimeException('Token inválido o expirado');

        if(empty($password)) throw new InvalidArgumentException('Contraseña vacía');

        if($password !== $rePassword) throw new InvalidArgumentException('Las contraseñas no coinciden');

        $user->set('password', $password);
        $user->set('token', null);

        return $user->save(null);
    }

}
