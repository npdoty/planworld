##
# Mods to sexy_migrations (http://errtheblog.com/post/2381)
module MojoDNA
  module AttachmentMigration
    # columns for attachment_fu (http://svn.techno-weenie.net/projects/plugins/attachment_fu/)
    def attachment
      integer :parent_id
      string  :content_type
      string  :filename
      string  :thumbnail
      integer :size
      integer :width
      integer :height
    end
    alias :attachment! :attachment
  end
end

ActiveRecord::ConnectionAdapters::TableDefinition.send :include, MojoDNA::AttachmentMigration
