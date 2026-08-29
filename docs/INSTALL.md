# Installation

[English](INSTALL.md) | [Česky](INSTALL_CZ.md) | [← Documentation](README.md)

This page describes installation of Sticky Notes 1.1.0 from the source repository. The application supports **Nextcloud 34**.

## 1. Requirements

For installation on a NAS you will need:

- a working Nextcloud 34 installation,
- access to the NAS,
- SSH/SFTP enabled,
- an account with sufficient privileges to run the installation script,
- the downloaded Sticky Notes project.

On Windows, **WinSCP** is a convenient option for transferring the project to the NAS. It is a free open-source client supporting SFTP/SCP and other protocols.

Official website: https://winscp.net/

> Recommendation: use **SFTP** rather than unencrypted FTP for transfers to the NAS. SFTP normally runs over SSH and commonly uses port 22 unless the server administrator configured another port.

## 2. Download Sticky Notes

There are two common ways to obtain the project.

### Option A – download from GitHub

Open the Sticky Notes repository:

https://github.com/hacesoft/nextcloud-stickynotes

To download the source tree, GitHub provides **Code → Download ZIP**. If a dedicated release package is available for the version you want, prefer the corresponding release.

Extract the downloaded archive on your computer.

### Option B – Git

If Git is available on the target system, clone the repository directly:

```sh
cd /volume1/docker
git clone https://github.com/hacesoft/nextcloud-stickynotes.git
cd nextcloud-stickynotes
```

`/volume1/docker` is only an example suitable for a Synology NAS. The project may be stored elsewhere.

## 3. Transfer to a Synology NAS with WinSCP

If the project was downloaded to Windows, WinSCP can be used to copy it to the NAS.

### Enable SSH on Synology

In DSM open:

**Control Panel → Terminal & SNMP → Terminal**

and enable the **SSH service**.

You can disable SSH again after installation if you do not normally need it.

### Connect with WinSCP

Create a new WinSCP connection:

- **File protocol:** SFTP
- **Host name:** IP address or DNS name of your NAS
- **Port:** 22 unless SSH is configured on another port
- **User name:** your NAS account
- **Password:** the account password

On the first connection, WinSCP displays the server's SSH host-key fingerprint. Verify that you are connecting to the intended NAS before permanently accepting the key.

### Where to upload the project

For example:

```text
/volume1/docker/nextcloud-stickynotes/
```

Upload the **whole project** to this directory, for example:

```text
nextcloud-stickynotes/
├── src/
├── docs/
├── release/
├── old/
├── install.sh
├── CHANGELOG.md
├── LICENSE
├── README.md
└── README_CZ.md
```

> Do not manually copy the whole repository into Nextcloud's `custom_apps` directory. The installation script takes only the application runtime files from `src/` and deploys them to the correct `custom_apps/stickynotes` directory.

## 4. Connect using SSH

After uploading the project, connect to the NAS using SSH.

On Windows you can use Windows Terminal or PowerShell:

```sh
ssh user@NAS_IP_ADDRESS
```

Then change to the project directory:

```sh
cd /volume1/docker/nextcloud-stickynotes
```

## 5. Run the installer

From the project root run:

```sh
sudo sh install.sh
```

The installer uses the application source tree under `src/`.

For the supported Docker deployment it:

1. checks the required runtime directories,
2. prepares a clean deployment tree,
3. deploys the application as `custom_apps/stickynotes`,
4. applies the required file ownership,
5. enables the application,
6. runs the required Nextcloud `occ` operations,
7. verifies the deployed version and runtime files.

The supplied installer targets the Docker-based Nextcloud deployment for which this project was created. For a different Nextcloud installation, review and adapt `install.sh` before running it.

## 6. Verify the installation

After successful installation:

1. sign in to Nextcloud,
2. verify that Sticky Notes is available,
3. open the application,
4. create a test note,
5. optionally add the Sticky Notes widget to the Dashboard.

The application version is defined in:

```text
src/appinfo/info.xml
```

## 7. Future updates

If you use Git:

```sh
cd /volume1/docker/nextcloud-stickynotes
git pull
sudo sh install.sh
```

If you use WinSCP, download the new project version, replace the project source files on the NAS, and run again:

```sh
sudo sh install.sh
```

Keep a current backup of Nextcloud and its database before updating. See [UPDATE.md](UPDATE.md) for more information.

## Security note

Do not unnecessarily expose SSH/SFTP directly to the public Internet. For a typical home installation, perform administration from the local network or through a trusted VPN connection. You can disable SSH again after maintenance if you do not need it.
