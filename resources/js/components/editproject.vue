<style scoped>
    .action-link {
        cursor: pointer;
    }

    .m-b-none {
        margin-bottom: 0;
    }
</style>

<template>
    <div>
        <div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            Personal Access Tokens
                        </span>

                        <a class="action-link" @click="showCreateTokenForm">
                            Create New Token
                        </a>
                    </div>
                </div>

                <div class="panel-body">
                    <!-- No Tokens Notice -->
                    <p class="m-b-none" v-if="tokens.length === 0">
                        You have not created any personal access tokens.
                    </p>

                    <!-- Personal Access Tokens -->
                    <table class="table table-borderless m-b-none" v-if="tokens.length > 0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="token in tokens">
                                <!-- Client Name -->
                                <td style="vertical-align: middle;">
                                    {{ token.name }}
                                </td>

                                <!-- Delete Button -->
                                <td style="vertical-align: middle;">
                                    <a class="action-link text-danger" @click="revoke(token)">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Token Modal -->
        <div class="modal" id="modal-create-token" tabindex="-1" role="dialog" :class="[tokenModal ? 'is-active' : '']">
          <div class="modal-background"></div>
            <div class="modal-card">
                <div class="modal-card-head">
                    <h4 class="modal-card-title">
                        Create Token
                    </h4>
                    <a class="delete" @click.prevent="closeTokenModal"></a>
                </div>

                <div class="modal-card-body">
                    <!-- Form Errors -->
                    <div class="notification is-danger" v-if="form.errors.length > 0">
                        <strong>Whoops! Something went wrong!</strong>
                        <br>
                        <ul>
                            <li v-for="error in form.errors">
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <!-- Create Token Form -->
                    <form class="form-horizontal" role="form" @submit.prevent="store">
                        <!-- Name -->
                        <div class="field">
                          <label class="label">Name</label>
                          <p class="control">
                            <input id="create-token-name" type="text" class="input" name="name" v-model="form.name">
                          </p>
                        </div>

                        <!-- Scopes -->
                        <div class="form-group" v-if="scopes.length > 0">
                            <label class="col-md-4 control-label">Scopes</label>

                            <div class="col-md-6">
                                <div v-for="scope in scopes">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox"
                                                @click="toggleScope(scope.id)"
                                                :checked="scopeIsAssigned(scope.id)">

                                                {{ scope.id }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Actions -->
                <div class="modal-card-foot">
                    <a class="button" @click.prevent="closeTokenModal">Close</a>
                    <a class="button is-primary"@click="store">Create</a>
                </div>
            </div>

        </div>

        <!-- Access Token Modal -->
        <div class="modal" id="modal-access-token" tabindex="-1" role="dialog" :class="[accessTokenModal ? 'is-active' : '']">
            <div class="modal-background"></div>
            <div class="modal-card">

              <div class="modal-card-head">
                  <h4 class="modal-card-title">
                      Personal Access Token
                  </h4>
                  <a class="delete" @click.prevent="closeAccessTokenModal"></a>
              </div>

              <div class="modal-card-body">
                  <p>
                      Here is your new personal access token. This is the only time it will be shown so don't lose it!
                      You may now use this token to make API requests.
                  </p>

                  <pre><code>{{ accessToken }}</code></pre>
              </div>

                <!-- Modal Actions -->
              <div class="modal-card-foot">
                  <a class="button" @click.prevent="closeAccessTokenModal">Close</a>
              </div>
          </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    export default {
        /*
         * The component's data.
         */
        data() {
            return {
                accessToken: null,

                tokens: [],
                scopes: [],

                form: {
                    name: '',
                    scopes: [],
                    errors: []
                },
                tokenModal: false,
                accessTokenModal: false
            };
        },

        /**
         * Prepare the component (Vue 1.x).
         */
        ready() {
            this.prepareComponent();
        },

        /**
         * Prepare the component (Vue 2.x).
         */
        mounted() {
            this.prepareComponent();
        },

        methods: {
            /**
             * Prepare the component.
             */
            prepareComponent() {
                this.getTokens();
                this.getScopes();
            },

            /**
             * Get all of the personal access tokens for the user.
             */
            getTokens() {
                axios.get('/oauth/personal-access-tokens')
                        .then(response => {
                            this.tokens = response.data;
                        });
            },

            /**
             * Get all of the available scopes.
             */
            getScopes() {
                axios.get('/oauth/scopes')
                        .then(response => {
                            this.scopes = response.data;
                        });
            },

            /**
             * Show the form for creating new tokens.
             */
            showCreateTokenForm() {
                this.tokenModal = true
            },

            /**
             * Create a new personal access token.
             */
            store() {
                this.accessToken = null;

                this.form.errors = [];

                axios.post('/oauth/personal-access-tokens', this.form)
                        .then(response => {
                            this.form.name = '';
                            this.form.scopes = [];
                            this.form.errors = [];

                            this.tokens.push(response.data.token);

                            this.showAccessToken(response.data.accessToken);
                        })
                        .catch(response => {
                            if (typeof response.data === 'object') {
                                this.form.errors = _.flatten(_.toArray(response.data));
                            } else {
                                this.form.errors = ['Something went wrong. Please try again.'];
                            }
                        });
            },

            /**
             * Toggle the given scope in the list of assigned scopes.
             */
            toggleScope(scope) {
                if (this.scopeIsAssigned(scope)) {
                    this.form.scopes = _.reject(this.form.scopes, s => s == scope);
                } else {
                    this.form.scopes.push(scope);
                }
            },

            /**
             * Determine if the given scope has been assigned to the token.
             */
            scopeIsAssigned(scope) {
                return _.indexOf(this.form.scopes, scope) >= 0;
            },

            /**
             * Show the given access token to the user.
             */
            showAccessToken(accessToken) {
                this.closeTokenModal()

                this.accessToken = accessToken;

                this.accessTokenModal = true
            },

            /**
             * Revoke the given token.
             */
            revoke(token) {
                axios.delete('/oauth/personal-access-tokens/' + token.id)
                        .then(response => {
                            this.getTokens();
                        });
            },

            closeTokenModal () {
              this.tokenModal = false
            },

            closeAccessTokenModal () {
              this.accessTokenModal = false
            }
        }
    }
</script>
