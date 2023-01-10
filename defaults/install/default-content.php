<!--
        Target: default-content
        Installs default content for testing the site.
      -->
      <target name="default-content" description="Creates default site content for non-Hubb data.">
      <!-- Create demo content -->
      <trycatch property="install">
        <try>
          <drush command="pm-enable" assume="yes">
            <param>ioep23_default_content</param>
          </drush>
        </try>
        <catch>
          <echo>Content already imported.</echo>
        </catch>
      </trycatch>
      <trycatch property="uninstall">
        <try>
          <!-- Uninstall the demo content modules -->
          <drush command="pm-uninstall" assume="yes">
            <param>ioep23_default_content</param>
            <param>default_content</param>
          </drush>
        </try>
        <catch>
          <echo>Modules never enabled.</echo>
        </catch>
      </trycatch>
    </target>
    