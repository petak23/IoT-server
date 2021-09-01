# Komponenta: Prihlasovací formulár

## Inštalácia

1. nakopírovanie archývu do `app\components\User`,
2. do presenteru doplniť `use PeterVojtech\User\Sign\signInTrait;`,
3. do `app\config\components.neon` doplniť:

```neon
parameters:
  components:
#...
    signIn:
      name: 'Formulár pre prihlásenie'
      unique: false
      fa_icon: 'log-in'
#...

services:

# Component SignIn
  - PeterVojtech\User\Sign\ISignInControl
  - PeterVojtech\User\Sign\SignInFormFactory
```
