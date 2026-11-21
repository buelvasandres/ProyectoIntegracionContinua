pipeline {
  agent any

  options { disableConcurrentBuilds() }

  environment {
    COMPOSE_PROJECT_NAME = 'ci'
    COMPOSE_FILE = 'docker-compose.yml:docker-compose.ci.yml'
  }

  stages {
    stage('Preparar') {
      steps {
        sh '''
          docker version
          docker compose version
          cp .env.example .env
          docker compose down -v --remove-orphans || true
        '''
      }
    }

    stage('Construir imágenes') {
      steps { sh 'docker compose build' }
    }

    stage('Levantar servicios') {
      steps { sh 'docker compose up -d --wait' }
    }

    stage('Pruebas') {
      steps {
        sh '''
          echo "--- GET /health"
          for i in $(seq 1 15); do
            docker compose exec -T api curl -fsS localhost:5000/health && break
            sleep 2
          done
          docker compose exec -T api curl -fsS localhost:5000/health

          echo "--- GET /usuarios"
          docker compose exec -T api curl -fsS localhost:5000/usuarios | tee /tmp/usuarios.json
          grep -q Pablo /tmp/usuarios.json

          echo "--- POST /usuarios"
          docker compose exec -T api curl -fsS -X POST localhost:5000/usuarios \
            -H "Content-Type: application/json" \
            -d '{"nombre":"Jenkins","apellido":"CI","edad":30,"sexo":"M"}'

          echo "--- Verificacion en PostgreSQL"
          docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -c "SELECT * FROM usuarios;"' | tee /tmp/db.txt
          grep -q Jenkins /tmp/db.txt
        '''
      }
    }
  }

  post {
    always {
      sh '''
        docker compose ps || true
        docker compose logs --no-color --tail=50 || true
        docker compose down -v --remove-orphans || true
        rm -f .env
      '''
    }
  }
}