# Production Deployment

Pushing to `main` runs tests and deploys LeadForge AI to the configured server.

## One-time Server Setup

1. Enable SSH access for the production Hostinger account.
2. Upload the application once to the deployment directory, such as `/home/USER/domains/leadai.aparkitsolutions.com/public_html`.
3. Keep the production `.env` file in that directory. It is intentionally never uploaded by the workflow.
4. Ensure PHP 8.3+, Composer, and the required PHP extensions are available on the server.
5. Set the web root to the application's `public` directory when the project is stored outside `public_html`.
6. Give the web-server user write access to `storage` and `bootstrap/cache`.

## GitHub Secrets

Create these repository secrets in **Settings > Secrets and variables > Actions**:

| Secret | Value |
| --- | --- |
| `DEPLOY_HOST` | Server hostname or IP address |
| `DEPLOY_PORT` | SSH port, normally `22` |
| `DEPLOY_USER` | SSH username |
| `DEPLOY_PATH` | Absolute path to the Laravel project on the server |
| `DEPLOY_SSH_PRIVATE_KEY` | Private key that can log in as `DEPLOY_USER` |

Add the matching public key to `~/.ssh/authorized_keys` for `DEPLOY_USER` on the server. Use a dedicated deployment key without a passphrase and do not reuse the GitHub key for personal SSH access.

## Deploying

Push a commit to `main`, then review the **Test and Deploy** workflow in the repository Actions tab. It uploads application code, preserves `.env` and persistent Laravel directories, installs production Composer dependencies, runs migrations, and refreshes Laravel caches.

Use **Run workflow** in the Actions tab to deploy the current `main` branch without a new commit.