# Instalace

[Česky](INSTALL_CZ.md) | [English](INSTALL.md) | [← Dokumentace](README_CZ.md)

Tato stránka popisuje instalaci Sticky Notes 1.0.0 ze zdrojového repozitáře. Aplikace podporuje **Nextcloud 34**.

## 1. Co budete potřebovat

Pro instalaci na NAS budete potřebovat:

- funkční Nextcloud 34,
- přístup k NASu,
- povolené SSH/SFTP,
- účet s oprávněním potřebným pro spuštění instalačního skriptu,
- stažený projekt Sticky Notes.

Na Windows je pro kopírování souborů na NAS vhodný například **WinSCP**. Je to bezplatný open-source klient pro SFTP/SCP a další protokoly.

Oficiální web: https://winscp.net/

> Doporučení: pro přenos na NAS používejte **SFTP**, nikoli nešifrované FTP. SFTP standardně používá SSH a obvykle port 22, pokud správce serveru nenastavil jiný port.

## 2. Stažení Sticky Notes

Projekt je možné získat dvěma způsoby.

### Varianta A – stažení přes web GitHubu

Otevřete repozitář Sticky Notes:

https://github.com/hacesoft/nextcloud-stickynotes

Pro stažení zdrojového stromu můžete na GitHubu použít **Code → Download ZIP**. Pokud je pro požadovanou verzi zveřejněn samostatný release balíček, použijte raději odpovídající release.

Stažený archiv rozbalte v počítači.

### Varianta B – Git

Pokud máte na cílovém systému Git, můžete repozitář naklonovat přímo:

```sh
cd /volume1/docker
git clone https://github.com/hacesoft/nextcloud-stickynotes.git
cd nextcloud-stickynotes
```

Cesta `/volume1/docker` je pouze příklad vhodný pro Synology NAS. Projekt může být uložen i jinde.

## 3. Přenos na Synology NAS pomocí WinSCP

Pokud jste projekt stáhli do Windows, můžete jej na NAS jednoduše přenést pomocí WinSCP.

### Povolení SSH na Synology

V DSM otevřete:

**Ovládací panel → Terminál a SNMP → Terminál**

a povolte službu **SSH**.

Po dokončení instalace můžete SSH opět vypnout, pokud jej běžně nepotřebujete.

### Připojení ve WinSCP

Ve WinSCP vytvořte nové připojení:

- **File protocol:** SFTP
- **Host name:** IP adresa nebo DNS jméno vašeho NASu
- **Port:** 22, pokud jste SSH nenastavili na jiný port
- **User name:** váš účet na NASu
- **Password:** heslo tohoto účtu

Při prvním připojení WinSCP zobrazí otisk SSH klíče serveru. Před jeho trvalým potvrzením je vhodné ověřit, že se skutečně připojujete ke svému NASu.

### Kam projekt nahrát

Například:

```text
/volume1/docker/nextcloud-stickynotes/
```

Do tohoto adresáře nahrajte **celý projekt**, tedy například:

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

> Celý repozitář nekopírujte ručně do adresáře Nextcloudu `custom_apps`. Instalační skript vezme z `src/` pouze runtime soubory aplikace a nasadí je do správného adresáře `custom_apps/stickynotes`.

## 4. Přihlášení přes SSH

Po nahrání projektu se přihlaste k NASu přes SSH.

Ve Windows můžete použít například Windows Terminal nebo PowerShell:

```sh
ssh uzivatel@IP_ADRESA_NASU
```

Potom přejděte do adresáře projektu:

```sh
cd /volume1/docker/nextcloud-stickynotes
```

## 5. Spuštění instalace

Z kořenového adresáře projektu spusťte:

```sh
sudo sh install.sh
```

Instalátor pracuje se zdrojovým stromem `src/`.

Při podporovaném Docker nasazení:

1. zkontroluje potřebné runtime adresáře,
2. připraví čistý instalační strom,
3. nasadí aplikaci jako `custom_apps/stickynotes`,
4. nastaví potřebné vlastnictví souborů,
5. aktivuje aplikaci,
6. spustí potřebné Nextcloud `occ` operace,
7. ověří nasazenou verzi a runtime soubory.

Instalační skript je připraven pro Dockerové nasazení Nextcloudu, pro které byl projekt vytvořen. U jiné instalace Nextcloudu je nutné před spuštěním zkontrolovat a případně upravit `install.sh`.

## 6. Ověření instalace

Po úspěšné instalaci:

1. přihlaste se do Nextcloudu,
2. ověřte, že je Sticky Notes mezi aplikacemi,
3. otevřete aplikaci,
4. vytvořte testovací lísteček,
5. případně přidejte Sticky Notes widget na Dashboard.

Verze aplikace je definována v:

```text
src/appinfo/info.xml
```

## 7. Pozdější aktualizace

Pokud používáte Git:

```sh
cd /volume1/docker/nextcloud-stickynotes
git pull
sudo sh install.sh
```

Pokud používáte WinSCP, stáhněte novou verzi projektu, nahraďte zdrojové soubory projektu na NASu a znovu spusťte:

```sh
sudo sh install.sh
```

Před aktualizací doporučujeme mít aktuální zálohu Nextcloudu a databáze. Další informace jsou v [UPDATE_CZ.md](UPDATE_CZ.md).

## Poznámka k bezpečnosti

SSH/SFTP nevystavujte zbytečně přímo do internetu. Pro běžnou domácí instalaci provádějte správu ideálně z lokální sítě nebo přes důvěryhodné VPN připojení. Po dokončení práce můžete SSH na NASu opět vypnout, pokud jej nepotřebujete.
