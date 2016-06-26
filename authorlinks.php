<?php
// no direct access
defined( '_JEXEC' ) or die;

class plgContentAuthorlinks extends JPlugin
{
        /**
        * Load the language file on instantiation. Note this is only available in Joomla 3.1 and higher.
        * If you want to support 3.0 series you must override the constructor
        *
        * @var    boolean
        * @since  3.1
        */
        protected $autoloadLanguage = true;

        /**
        * Link author name to various contact details
        *
        * @param   string   $context  The context of the content being passed to the plugin.
        * @param   mixed    &$row     An object with a "text" property
        * @param   mixed    $params   Additional parameters.
        * @param   integer  $page     Optional page number. Unused. Defaults to zero.
        *
        * @return  boolean	True on success.
        */
        public function onContentPrepare($context, &$row, $params, $page = 0)
        {
                $allowed_contexts = array('com_content.category', 'com_content.article', 'com_content.featured');

                if (!in_array($context, $allowed_contexts))
                {
                        return true;
                }

                // Return if we don't link the author
                if (!$params->get('link_author'))
                {
                        return true;
                }

                // Return if we don't have a valid article id
                if (!isset($row->id) || !(int) $row->id)
                {
                        return true;
                }
                
                // Return if an alias is used
                if ($this->params->get('LinkToAlias') == 0 & $row->created_by_alias != '')
                {
                        return true;
                }
                
                switch ($this->params->get('UrlType')) {
                //Link author name to internal contact page
                case 0:
                    $row->contactid = $this->_getContactId($row->created_by);
                    if ($row->contactid)
                    {
                        $needle = 'index.php?option=com_contact&view=contact&id=' . $row->contactid;
                        $menu = JFactory::getApplication()->getMenu();
                        $item = $menu->getItems('link', $needle, true);
                        $link = $item ? $needle . '&Itemid=' . $item->id : $needle;
                        $row->contact_link = JRoute::_($link);
                    }
                    else
                    {
                        $row->contact_link = '';
                    }
                    break;
                
                //Link author name to webpage specified in associated contact
                case 1:
                    $url = $this->_getAuthorLink($row->created_by);
                    if ($url)
                    {
                        $row->contact_link = $url;
                    }
                    else
                    {
                        $row->contact_link = '';
                    }
                    break;
                
                //Link author name to email specified in associated contact
                case 2:
                    $email = $this->_getAuthorEmail($row->created_by);
                    if ($email)
                    {
                        $row->contact_link = 'mailto:' . $email;
                    }
                    else
                    {
                        $row->contact_link = '';
                    }
                    break;
                }
                return true;
        }
        
        
        /**
        * Get contact ID from author ID
        *
        * @param   string   $created_by  The article author ID.
        *
        * @return  integer	The associated contact ID.
        */
        protected function _getContactId($created_by)
        {
                $db = JFactory::getDbo();
                
                $query = $db->getQuery(true);

                $query->select($db->quoteName('id'));
                $query->from($db->quoteName('#__contact_details'));
                $query->where('published = 1');
                $query->where($db->quoteName('user_id') . ' = ' . (int) $created_by);

                $db->setQuery($query);
                $result = $db->loadResult();
                
                return $result;
        }
        
        /**
        * Get webpage from the associated contact ID
        *
        * @param   string   $author_id  The article author ID.
        *
        * @return  string	The webpage of the associated contact ID.
        */
        protected function _getAuthorLink($author_id)
        {
                $db = JFactory::getDbo();
                
                $query = $db->getQuery(true);
                
                $query->select($db->quoteName('webpage'));
                $query->from($db->quoteName('#__contact_details'));
                $query->where($db->quoteName('user_id') . ' = ' . (int) $author_id);
                
                $db->setQuery($query);
                $result = $db->loadResult();
                
                return $result;
        }
        
        /**
        * Get email from the associated contact ID
        *
        * @param   string   $author_id  The article author ID.
        *
        * @return  string	The email of the associated contact ID.
        */
        protected function _getAuthorEmail($author_id)
        {
                $db = JFactory::getDbo();
                
                $query = $db->getQuery(true);
                
                $query->select($db->quoteName('email_to'));
                $query->from($db->quoteName('#__contact_details'));
                $query->where($db->quoteName('user_id') . ' = ' . (int) $author_id);
                
                $db->setQuery($query);
                $result = $db->loadResult();
                
                return $result;
        }
}
?>
