module.exports = (grunt) ->
  grunt.initConfig
    useminPrepare:
      html: ['app/presenters/templates/@layout.latte']
      options:
        dest: '.'

    netteBasePath:
      basePath: 'www'
      options:
        removeFromPath: ['app/presenters/templates/']

  # These plugins provide necessary tasks.
  grunt.loadNpmTasks 'grunt-contrib-concat'
  grunt.loadNpmTasks 'grunt-contrib-uglify'
  grunt.loadNpmTasks 'grunt-contrib-cssmin'
  grunt.loadNpmTasks 'grunt-usemin'
  grunt.loadNpmTasks 'grunt-nette-basepath'

  # Default task.
  grunt.registerTask 'default', [
    'useminPrepare'
    'netteBasePath'
    'concat'
    'uglify'
    'cssmin'
  ]