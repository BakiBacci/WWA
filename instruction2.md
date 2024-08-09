# Création de la section Profil

> Je veux un lien dans le header profil. Ce lien n'apparaît que si l'utilisateur est connecté. Ce lien doit rediriger vers la page de profil de l'utilisateur connecté.

**Route => Controller**

Cette route est protégée, il faut être connecté pour y accéder :

-   **/profil**

    -   **ProfileController**

        -   **/index**

            -   Affiche la liste des factures de l'utilisateur.
            -   `$id = $this->getUsers()->getId()`
            -   `order->findAllUserOrders($id)`
            -   `where order.user = user.id`

        -   **/detail/{id} show**
            -   Affiche le détail d'une facture.

Nicolas
Kenza
Assa
Hugo
Oura
Siapri
Eliesse
Sofian
Said
