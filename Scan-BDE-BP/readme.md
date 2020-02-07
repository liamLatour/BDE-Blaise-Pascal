
# Installation des prérequis

* [Node](https://nodejs.org/en/), nécessaire à l'installation de Cordova

* Cordova, dans l'invite de commande tapez `npm install -g cordova`

* Pour Android, suivez les instructions de la rubrique [Installing the Requirements](https://cordova.apache.org/docs/en/latest/guide/platforms/android/)

# Modifications nécéssaires

Trouvez le fichier AndroidManifest.xml dans "/platforms/android/app/src/main/"

Ajoutez ces lignes avant `</manifest>`:

    <uses-permission android:name="android.permission.CAMERA" android:required="false" />
    <uses-feature android:name="android.hardware.camera" android:required="false" />
    <uses-feature android:name="android.hardware.camera.front" android:required="false" />
    <uses-feature android:name="android.hardware.camera.autofocus" />
    <uses-feature android:name="android.hardware.camera2.full" />
    <uses-feature android:name="android.hardware.camera2.autofocus" />
    <uses-permission android:name="android.permission.INTERNET" />
    <uses-permission android:name="android.webkit.PermissionRequest" />

# Tester l'application

* Ouvrez l'invite de commande et rendez-vous dans le dossier `Scan-BDE-BP`

* Ajoutez Android aux platforms avec: `cordova platform add android`

* Puis lancer celle-ci pour créer un .apk de test avec: `cordova run android`

# Comment créer un fichier .apk signé

* Créez un fichier .keystore si vous n'en n'avez pas déjà un (il est recommandé d'utiliser un seul keystore)

* Créer une .apk avec: `cordova build --release android`

* Signez l'apk avec: `C:/chemin/java/jdk-xx.x.x/bin/jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore C:/chemin/du/keystore/KEY.keystore C:/chemin/Scan-BDE-BP/platforms/android/app/build/outputs/apk/release/app-release-unsigned.apk NomDeApp`

* Optimiser l'app avec: `C:/chemin/Android/Sdk/build-tools/xx.x.x\zipalign -v 4 C:/chemin/app-release-unsigned.apk "NomApp.apk"`

* Votre .apk signé se trouve dans le même dossier que `C:/chemin/app-release-unsigned.apk`