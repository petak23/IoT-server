# Komponenta pre editovanie ACL

*Vypísanie a editácia ACL webu. *

**Inštalácia**
1. nakopírovanie archývu do `app\components`,
2. do `app\Presenters\UserAclPresenter` doplniť `use PeterVojtech\UserAcl\userAclEditTrait;`,
4. do `app\config\components.neon` doplniť:
```neon
parameters:
  components:
#...
    userAclEdit:
      name: 'Editovanie ACL'
      unique: TRUE
      fa_icon: 'user-shield'
#...

services:
# Component UserAcl
  - PeterVojtech\UserAcl\IAdminUserAcl
  - PeterVojtech\UserAcl\EditRoleFormFactory
```